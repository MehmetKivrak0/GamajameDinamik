# Yukarı Çık Butonu Oluşturma Rehberi

Bu rehber, sayfa kaydırıldığında görünen arcade tarzı animasyonlu bir "yukarı çık" butonu oluşturmayı açıklar.

## HTML Yapısı

```html
<!-- Yukarı Çık Butonu -->
<button id="scrollToTop" class="position-fixed d-none">
    <div class="scroll-btn-wrapper">
        <div class="scroll-btn-circle">
            <svg class="ghost-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
                <path fill="currentColor" d="M186.1.09C81.01 3.24 0 94.92 0 200.05v263.92c0 14.26 17.23 21.39 27.31 11.31l24.92-24.92c6.25-6.25 16.38-6.25 22.63 0l96 96c6.25 6.25 16.38 6.25 22.63 0l96-96c6.25-6.25 16.38-6.25 22.63 0l24.92 24.92c10.08 10.08 27.31 2.94 27.31-11.31V192C384 84 294.83-3.17 186.1.09zM128 224c-17.67 0-32-14.33-32-32s14.33-32 32-32 32 14.33 32 32-14.33 32-32 32zm128 0c-17.67 0-32-14.33-32-32s14.33-32 32-32 32 14.33 32 32-14.33 32-32 32z"/>
            </svg>
        </div>
        <div class="scroll-btn-rays"></div>
    </div>
</button>
```

### HTML Açıklaması
- `position-fixed`: Butonun sayfada sabit konumda durmasını sağlar
- `d-none`: Bootstrap sınıfı, başlangıçta butonu gizler
- `scroll-btn-wrapper`: Buton ve ışık efektleri için konteyner
- `scroll-btn-circle`: Ana buton görünümü
- `scroll-btn-rays`: Arkadaki parıltı efekti
- `ghost-icon`: Özel SVG hayalet ikonu, beyaz arka plan sorunu olmadan

## CSS Stilleri

```css
#scrollToTop {
    bottom: 30px;
    right: 30px;
    z-index: 9999;
    width: 50px;
    height: 50px;
    opacity: 1;
    transition: all 0.3s ease;
    cursor: pointer;
}

#scrollToTop.d-none {
    opacity: 0;
    pointer-events: none;
}

.scroll-btn-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
}

.scroll-btn-circle {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    border: 2px solid #c9d011;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    color: #c9d011;
    font-size: 1.5rem;
    z-index: 2;
}

.scroll-btn-rays {
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    border: 2px solid #c9d011;
    border-radius: 50%;
    z-index: 1;
    animation: pulse 1.5s infinite;
    opacity: 0;
}

/* İkon Stilleri ve Arka Plan Düzeltmesi */
.scroll-btn-circle .fa-ghost {
    font-size: 1.8rem;
    transition: all 0.3s ease;
    background: transparent !important;
    -webkit-background-clip: text;
    -webkit-text-fill-color: #c9d011;
    -webkit-font-smoothing: antialiased;
    display: inline-block;
    will-change: transform;
    filter: drop-shadow(0 0 2px rgba(201, 208, 17, 0.5));
}

.fa-ghost::before {
    content: "\f6e2";
    background: transparent !important;
    -webkit-background-clip: text;
    -moz-background-clip: text;
    background-clip: text;
    display: inline-block;
    color: transparent;
}

/* Animasyonlar */
@keyframes pulse {
    0% {
        transform: scale(1);
        opacity: 0.5;
    }
    100% {
        transform: scale(1.5);
        opacity: 0;
    }
}

@keyframes ghostFloat {
    0% {
        transform: translateY(0) scale(1);
    }
    25% {
        transform: translateY(-3px) scale(1.1) rotate(-5deg);
    }
    50% {
        transform: translateY(-5px) scale(1.15) rotate(5deg);
    }
    75% {
        transform: translateY(-3px) scale(1.1) rotate(-5deg);
    }
    100% {
        transform: translateY(0) scale(1) rotate(0deg);
    }
}

/* Hover Efektleri */
#scrollToTop:hover .scroll-btn-circle {
    background: #000;
    color: #c9d011;
    transform: scale(1.1);
    box-shadow: 0 0 10px rgba(201, 208, 17, 0.3);
}

#scrollToTop:hover .fa-ghost {
    animation: ghostFloat 1s ease-in-out infinite;
}
```

### CSS Açıklaması
1. **Temel Stiller**:
   - `transition`: Yumuşak geçiş efektleri
   - `opacity`: Görünürlük kontrolü
   - `pointer-events`: Gizliyken tıklamayı engeller

2. **Ghost İkonu Özellikleri**:
   - Font size 1.8rem ile optimal boyut
   - Arka plan tamamen şeffaf
   - Webkit özellikleri ile renk kontrolü
   - Drop-shadow ile neon efekti
   - Content ve color ayarları ile arka plan temizliği

3. **Arcade Tarzı Animasyon**:
   - Yukarı-aşağı yüzme hareketi
   - Hafif dönme efekti (5 derece)
   - Boyut değişimi ile canlılık
   - 1 saniye döngü süresi

## JavaScript Kodu

```javascript
// Buton elemanını seç
const scrollToTopBtn = document.getElementById("scrollToTop");

// Sayfa kaydırma olayını dinle
window.addEventListener("scroll", function() {
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        scrollToTopBtn.classList.remove("d-none");
    } else {
        scrollToTopBtn.classList.add("d-none");
    }
});

// Butona tıklama olayı
scrollToTopBtn.addEventListener("click", function() {
    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
});
```

## Önemli Notlar

### SVG İkon İpuçları
- SVG kullanmanın avantajları:
  1. Beyaz arka plan sorunu yok
  2. currentColor ile tema rengini otomatik alır
  3. Daha iyi performans
  4. Daha keskin görüntü
  5. Özelleştirilebilir boyut ve renk

### Genel Notlar
- SVG ghost ikonu ile temiz görünüm
- Drop-shadow ile neon efektleri
- Yumuşak animasyonlar
- Tema rengine otomatik uyum
- Retro arcade tarzı tasarım