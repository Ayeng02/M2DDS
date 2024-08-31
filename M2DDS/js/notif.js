document.querySelector('.notification-bell').addEventListener('click', function() {
    var overlay = document.getElementById('notificationOverlay');
    overlay.style.display = (overlay.style.display === 'none' || overlay.style.display === '') ? 'block' : 'none';
});

window.onclick = function(event) {
    if (!event.target.matches('.notification-bell, .notification-bell *')) {
        var overlay = document.getElementById('notificationOverlay');
        if (overlay.style.display === 'block') {
            overlay.style.display = 'none';
        }
    }
}