<?php
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . base_path('/login.php'));
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'user';

// Ensure chat table exists (helps for fresh installs)
function ensure_chat_table_exists($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS chat (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $r = mysqli_query($conn, $sql);
    if ($r === false) {
        error_log('Failed to ensure chat table: ' . mysqli_error($conn));
        return false;
    }
    return true;
}
if (!ensure_chat_table_exists($conn)) {
    // fatal for chat -- but allow page to render with no chat functionality
    error_log('Chat: unable to create/verify chat table');
}

// Get admin ID (assuming first admin)
// Get admin ID (assuming first admin)
$admin_query = mysqli_query($conn, "SELECT id FROM users WHERE role='admin' LIMIT 1");
if ($admin_query === false) {
    error_log('DB error (admin query): ' . mysqli_error($conn));
    die('Terjadi kesalahan pada server (database).');
}
$admin = mysqli_fetch_assoc($admin_query);
$admin_id = $admin['id'] ?? null;

if (!$admin_id) {
    die('Admin tidak ditemukan.');
}

// Determine chat counterpart (other party)
// For regular users, other is the admin. For admin users, allow selecting a user via GET 'user_id' or POST 'receiver_id', otherwise pick the first non-admin user.
$other_id = null;
$other_name = null;
if ($role === 'admin') {
    $other_id = (int)($_GET['user_id'] ?? $_POST['receiver_id'] ?? 0);
    if ($other_id <= 0) {
        // pick first non-admin user as default
        $uq = mysqli_query($conn, "SELECT id, fullname FROM users WHERE role!='admin' LIMIT 1");
        if ($uq && $rowu = mysqli_fetch_assoc($uq)) {
            $other_id = (int)$rowu['id'];
            $other_name = $rowu['fullname'];
        }
    } else {
        // fetch name
        $uq = mysqli_prepare($conn, "SELECT fullname FROM users WHERE id = ? LIMIT 1");
        if ($uq) {
            mysqli_stmt_bind_param($uq, 'i', $other_id);
            mysqli_stmt_execute($uq);
            $r = mysqli_stmt_get_result($uq);
            if ($r) {
                $rr = mysqli_fetch_assoc($r);
                $other_name = $rr['fullname'] ?? null;
            }
            mysqli_stmt_close($uq);
        }
    }
    if (empty($other_id)) {
        die('Tidak ada pengguna untuk diajak chat.');
    }
} else {
    $other_id = $admin_id;
}

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    // CSRF validation
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Token CSRF tidak valid']);
        exit;
    }
    $message = trim((string)($_POST['message'] ?? ''));
    if ($message === '') {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Pesan kosong']);
        exit;
    }

    $receiver_id = ($role === 'admin') ? (int)($_POST['receiver_id'] ?? 0) : (int)$admin_id;
    if ($receiver_id <= 0) {
        error_log('Chat: invalid receiver id');
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Penerima tidak valid']);
        exit;
    }

    // verify receiver exists
    $check = mysqli_prepare($conn, "SELECT id FROM users WHERE id = ? LIMIT 1");
    if ($check === false) {
        error_log('DB prepare failed (check receiver): ' . mysqli_error($conn));
        http_response_code(500);
        echo 'Server DB error';
        exit;
    }
    mysqli_stmt_bind_param($check, 'i', $receiver_id);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);
    $exists = mysqli_stmt_num_rows($check) > 0;
    mysqli_stmt_close($check);
    if (!$exists) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Penerima tidak ditemukan']);
        exit;
    }

    // Enforce reasonable length
    if (mb_strlen($message) > 2000) $message = mb_substr($message, 0, 2000);

    $stmt = mysqli_prepare($conn, "INSERT INTO chat (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    if ($stmt === false) {
        // fallback to direct insert (escaped) in case prepared statements fail on this environment
        error_log('DB prepare failed (insert chat), attempting fallback: ' . mysqli_error($conn));
        $sid = (int)$user_id;
        $rid = (int)$receiver_id;
        $msg_esc = mysqli_real_escape_string($conn, $message);
        $raw_sql = "INSERT INTO chat (sender_id, receiver_id, message) VALUES ($sid, $rid, '$msg_esc')";
        $qr = mysqli_query($conn, $raw_sql);
        if ($qr === false) {
            error_log('DB fallback insert failed (insert chat): ' . mysqli_error($conn) . ' -- SQL: ' . $raw_sql);
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Server DB error']);
            exit;
        }
        $insert_id = mysqli_insert_id($conn);
    } else {
        mysqli_stmt_bind_param($stmt, 'iis', $user_id, $receiver_id, $message);
        $exec = mysqli_stmt_execute($stmt);
        if ($exec === false) {
            error_log('DB execute failed (insert chat): ' . mysqli_error($conn));
            mysqli_stmt_close($stmt);
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan pesan']);
            exit;
        }
        $insert_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
    }

    // fetch inserted message to return to client
    $out = null;
    $q = mysqli_prepare($conn, "SELECT c.id, c.message, c.sender_id, c.receiver_id, c.created_at, u.fullname AS sender_name FROM chat c JOIN users u ON u.id = c.sender_id WHERE c.id = ? LIMIT 1");
    if ($q) {
        mysqli_stmt_bind_param($q, 'i', $insert_id);
        mysqli_stmt_execute($q);
        $res = mysqli_stmt_get_result($q);
        if ($res) $out = mysqli_fetch_assoc($res);
        mysqli_stmt_close($q);
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'ok', 'message' => 'Tersimpan', 'data' => $out]);
    exit;
}

// Get chat history between current user ($user_id) and the other party ($other_id)
$chat_query = mysqli_query($conn, "
    SELECT c.*, u.fullname AS sender_name
    FROM chat c
    JOIN users u ON u.id = c.sender_id
    WHERE (c.sender_id = $user_id AND c.receiver_id = $other_id)
       OR (c.sender_id = $other_id AND c.receiver_id = $user_id)
    ORDER BY c.created_at ASC
");

$chats = [];
if ($chat_query === false) {
    error_log('DB error (chat history): ' . mysqli_error($conn));
    // keep $chats empty to avoid fatal errors in the view
} else {
    while ($row = mysqli_fetch_assoc($chat_query)) {
        $chats[] = $row;
    }
}

$title = 'Chat dengan Admin';
require_once 'templates/header.php';
?>

<div class="container mt-4">
    <h2>Chat dengan Admin</h2>
    <?php if ($role === 'admin'): ?>
        <div style="margin-bottom:12px">
            <form method="get" style="display:flex;gap:8px;align-items:center">
                <label>Pilih user:</label>
                <select name="user_id" onchange="this.form.submit()">
                    <?php
                    $users_q = mysqli_query($conn, "SELECT id, fullname FROM users WHERE role!='admin' ORDER BY fullname ASC");
                    if ($users_q) {
                        while ($u = mysqli_fetch_assoc($users_q)) {
                            $sel = ((int)$u['id'] === (int)$other_id) ? 'selected' : '';
                            echo '<option value="'.(int)$u['id'].'" '.$sel.'>'.e($u['fullname']).'</option>';
                        }
                    }
                    ?>
                </select>
            </form>
        </div>
    <?php endif; ?>
    <div class="card">
        <div class="card-body" style="height: 400px; overflow-y: auto;" id="chat-box">
            <?php foreach ($chats as $chat): ?>
                <div class="mb-2">
                    <strong><?= e($chat['sender_name']) ?>:</strong> <?= e($chat['message']) ?>
                    <small class="text-muted">(<?= $chat['created_at'] ?>)</small>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="card-footer">
            <form method="post" id="chat-form">
                <?= csrf_field() ?>
                <input type="hidden" name="receiver_id" value="<?= (int)$other_id ?>">
                <div class="input-group">
                    <input type="text" name="message" class="form-control" placeholder="Ketik pesan..." required>
                    <button type="submit" class="btn btn-primary">Kirim</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('chat-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;
    const btn = form.querySelector('button[type=submit]');
    btn.disabled = true;
    const formData = new FormData(form);
    try {
        const res = await fetch('chat.php', { method: 'POST', body: formData });
        const json = await res.json();
        if (!res.ok || json.status !== 'ok') {
            alert(json.message || 'Gagal mengirim pesan');
            btn.disabled = false;
            return;
        }

        // append message to chat box
        const chatBox = document.getElementById('chat-box');
        const data = json.data;
        const div = document.createElement('div');
        div.className = 'mb-2';
        const time = data && data.created_at ? (' <small class="text-muted">(' + data.created_at + ')</small>') : '';
        div.innerHTML = '<strong>' + (data && data.sender_name ? escapeHtml(data.sender_name) : 'Anda') + ':</strong> ' + escapeHtml(form.message.value) + time;
        chatBox.appendChild(div);
        chatBox.scrollTop = chatBox.scrollHeight;
        form.message.value = '';
    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan jaringan');
    } finally {
        btn.disabled = false;
    }
});

function escapeHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
}
</script>

<?php require_once 'templates/footer.php'; ?>
