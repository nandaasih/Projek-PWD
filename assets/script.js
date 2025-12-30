// assets/script.js

// Confirm links
document.addEventListener("click", (e) => {
  const a = e.target.closest("[data-confirm]");
  if (a && !confirm(a.getAttribute("data-confirm"))) e.preventDefault();
});

// Toggle password visibility for inputs with .toggle-password
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.toggle-password');
  if (!btn) return;
  const targetId = btn.getAttribute('data-target');
  let input = null;
  if (targetId) input = document.getElementById(targetId);
  else input = btn.parentElement.querySelector('input[type="password"], input[type="text"]');
  if (!input) return;
  if (input.type === 'password'){
    input.type = 'text';
    btn.setAttribute('aria-label','Sembunyikan kata sandi');
  } else {
    input.type = 'password';
    btn.setAttribute('aria-label','Tampilkan kata sandi');
  }
});

// Live search for reservations list
document.addEventListener('DOMContentLoaded', () => {
  // ===== RESERVASI SEARCH =====
  const search = document.getElementById('reservasiSearch');
  const list = document.getElementById('reservationsList');
  if (search && list) {
    let timer = null;
    const noResults = document.getElementById('reservationsNoResults');
    
    function filterReservations(){
      const q = search.value.trim().toLowerCase();
      const items = Array.from(list.querySelectorAll('.reservation-item'));
      let visibleCount = 0;
      
      items.forEach(item => {
        const room = (item.getAttribute('data-room') || '').toLowerCase();
        const date = (item.getAttribute('data-date') || '').toLowerCase();
        const visible = q === '' || room.includes(q) || date.includes(q);
        item.style.display = visible ? '' : 'none';
        if (visible) visibleCount++;
      });
      
      if (noResults) {
        noResults.style.display = visibleCount > 0 ? 'none' : '';
      }
    }

    search.addEventListener('input', () => {
      if (timer) clearTimeout(timer);
      timer = setTimeout(filterReservations, 220);
    });

    // Clear button handler
    const clearBtn = document.getElementById('reservasiClear');
    if (clearBtn){
      clearBtn.addEventListener('click', (e) =>{
        e.preventDefault();
        search.value = '';
        filterReservations();
        search.focus();
      });
    }
  }

  // ===== DASHBOARD SEARCH (Ruangan) =====
  const dashboardSearch = document.getElementById('dashboardSearch');
  if (dashboardSearch) {
    let timer = null;
    
    function filterRooms(){
      const q = dashboardSearch.value.trim().toLowerCase();
      
      // Filter Active Reservations
      const activeList = document.querySelector('.active-res-list');
      if (activeList) {
        const activeCards = activeList.querySelectorAll('.active-res-card');
        let activeVisible = 0;
        
        activeCards.forEach(card => {
          const room = (card.querySelector('.res-room')?.textContent || '').toLowerCase();
          const visible = q === '' || room.includes(q);
          card.style.display = visible ? '' : 'none';
          if (visible) activeVisible++;
        });
        
        const noActive = activeList.parentElement.querySelector('.no-active');
        if (noActive && activeList) {
          noActive.style.display = activeVisible === 0 ? '' : 'none';
        }
      }
      
      // Filter Recent Reservations
      const recentList = document.getElementById('reservationsList');
      if (recentList) {
        const items = recentList.querySelectorAll('.reservation-item');
        let recentVisible = 0;
        
        items.forEach(item => {
          const room = (item.getAttribute('data-room') || '').toLowerCase();
          const visible = q === '' || room.includes(q);
          item.style.display = visible ? '' : 'none';
          if (visible) recentVisible++;
        });
        
        const noResults = document.getElementById('reservationsNoResults');
        if (noResults) {
          noResults.style.display = recentVisible > 0 ? 'none' : '';
        }
      }
    }

    dashboardSearch.addEventListener('input', () => {
      if (timer) clearTimeout(timer);
      timer = setTimeout(filterRooms, 220);
    });
  }

  // Floating action button: read `data-href` from element
  const fab = document.getElementById('fabCreate');
  if (fab) fab.addEventListener('click', () => {
    const href = fab.getAttribute('data-href') || '/user/reservasi_add.php';
    window.location.href = href;
  });

  // Load more reservations (AJAX)
  const loadMoreBtn = document.getElementById('loadMoreReservations');
  if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', async () => {
      let offset = parseInt(loadMoreBtn.getAttribute('data-offset') || '0', 10);
      const urlBase = loadMoreBtn.getAttribute('data-url');
      if (!urlBase) return;
      loadMoreBtn.disabled = true;
      const origText = loadMoreBtn.textContent;
      loadMoreBtn.textContent = 'Memuat...';
      try {
        const res = await fetch(urlBase + '?offset=' + offset, { credentials: 'same-origin' });
        if (!res.ok) throw new Error('Network error');
        const html = await res.text();
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        const items = wrapper.querySelectorAll('.reservation-item');
        const listEl = document.getElementById('reservationsList');
        if (items.length && listEl) {
          items.forEach(i => listEl.appendChild(i));
          offset += items.length;
          loadMoreBtn.setAttribute('data-offset', offset);
          // hide if fewer than page size
          if (items.length < 20) loadMoreBtn.style.display = 'none';
          // hide no-results if visible
          const noResults = document.getElementById('reservationsNoResults');
          if (noResults) noResults.style.display = 'none';
        } else {
          loadMoreBtn.style.display = 'none';
        }
      } catch (err) {
        console.error(err);
        alert('Gagal memuat data.');
      } finally {
        loadMoreBtn.disabled = false;
        loadMoreBtn.textContent = origText;
      }
    });
  }

    // Mark all notifications as read (elements with .mark-all-notif)
    const markAllBtns = document.querySelectorAll('.mark-all-notif');
    if (markAllBtns && markAllBtns.length) {
      markAllBtns.forEach(btn => btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Memproses...';
        const token = window.CSRF_TOKEN || '';
        try {
          const res = await fetch('/FINAL_P/actions/notif_mark_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: new URLSearchParams({ csrf_token: token, id: 'all' }),
            credentials: 'same-origin'
          });
          const data = await res.json();
          if (data && data.success) {
            // Update header badge(s)
            document.querySelectorAll('.nav-link .badge').forEach(b => b.remove());
            // Update local notif badge in page
            document.querySelectorAll('.notif-badge').forEach(nb => nb.remove());
            // Mark visual notifications as read
            document.querySelectorAll('.notification-card.notification-unread').forEach(n => n.classList.remove('notification-unread'));
          } else {
            console.error('Gagal mark all read', data);
          }
        } catch (err) {
          console.error(err);
        } finally {
          btn.disabled = false;
          btn.innerHTML = original;
        }
      }));
    }

    // Sidebar toggle (hamburger)
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
      sidebarToggle.addEventListener('click', (e) => {
        const sidebar = document.querySelector('.sidebar');
        const main = document.querySelector('.dashboard-main');
        if (sidebar) sidebar.classList.toggle('collapsed');
        if (main) main.classList.toggle('fullwidth');
      });
    }

});
