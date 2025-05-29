// Sayfa yüklendiğinde çalışacak fonksiyonlar
document.addEventListener('DOMContentLoaded', function() {
    loadUserData();
    loadUserEvents();
});

// Kullanıcı bilgilerini yükle
function loadUserData() {
    // Burada backend'den kullanıcı bilgileri alınacak
    // Örnek veri:
    const userData = {
        username: "KullaniciAdi",
        email: "kullanici@email.com"
    };

    document.getElementById('username').textContent = userData.username;
    document.getElementById('email').textContent = userData.email;
}

// Kullanıcının etkinliklerini yükle
function loadUserEvents() {
    // Burada backend'den etkinlik verileri alınacak
    // Örnek veriler:
    const events = [
        {
            name: "Oyun Geliştirme Yarışması",
            date: "2025-06-01",
            status: "Aktif"
        },
        {
            name: "Game Jam Etkinliği",
            date: "2025-07-15",
            status: "Beklemede"
        }
    ];

    const eventsList = document.getElementById('eventsList');
    eventsList.innerHTML = '';

    events.forEach(event => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${event.name}</td>
            <td>${formatDate(event.date)}</td>
            <td><span class="badge ${getStatusBadgeClass(event.status)}">${event.status}</span></td>
        `;
        eventsList.appendChild(row);
    });
}

// Profil güncelleme fonksiyonu
function updateProfile() {
    // Burada profil güncelleme modalı açılabilir veya
    // güncelleme sayfasına yönlendirilebilir
    alert('Profil güncelleme özelliği yakında eklenecek!');
}

// Tarih formatını düzenle
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('tr-TR', options);
}

// Durum badge'inin rengini belirle
function getStatusBadgeClass(status) {
    switch(status.toLowerCase()) {
        case 'aktif':
            return 'bg-success';
        case 'beklemede':
            return 'bg-warning';
        case 'tamamlandı':
            return 'bg-info';
        default:
            return 'bg-secondary';
    }
}

// Çıkış yapma fonksiyonu
function logout() {
    // Burada oturum sonlandırma işlemleri yapılacak
    window.location.href = '../php/login.php';
}
