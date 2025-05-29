# Yukarı Çık Butonu Kodları

## HTML Kodu
```html
<button id="scrollToTop" class="position-fixed d-none" style="bottom: 30px; right: 30px; z-index: 1000; width: 60px; height: 60px; border: none; background: none;">
    <div class="scroll-btn-wrapper">
        <div class="scroll-btn-circle">
            <i class="fas fa-rocket"></i>
        </div>
        <div class="scroll-btn-rays"></div>
    </div>
</button>
```

## CSS Kodu
```css
/* Yukarı Çık Butonu */
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
    background: rgba(0, 0, 0, 0.8);
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

#scrollToTop:hover .scroll-btn-circle {
    background: #c9d011;
    color: #000;
    transform: scale(1.1);
    box-shadow: 0 0 20px rgba(201, 208, 17, 0.7);
}

#scrollToTop:hover .fa-rocket {
    animation: rocketShake 0.5s infinite;
}

@keyframes rocketShake {
    0%, 100% {
        transform: rotate(-45deg);
    }
    50% {
        transform: rotate(-35deg);
    }
}

#scrollToTop.d-none {
    display: none !important;
}
```

## JavaScript Kodu
```javascript
const scrollToTopBtn = document.getElementById("scrollToTop");

window.addEventListener("scroll", function() {
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        scrollToTopBtn.style.display = "flex";
        setTimeout(() => scrollToTopBtn.classList.add("show"), 10);
    } else {
        scrollToTopBtn.classList.remove("show");
        setTimeout(() => {
            if (!scrollToTopBtn.classList.contains("show")) {
                scrollToTopBtn.style.display = "none";
            }
        }, 300);
    }
});

scrollToTopBtn.addEventListener("click", function() {
    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
});
```

## Özellikler

1. Tasarım:
   - Roket ikonu
   - Yarı saydam siyah arka plan
   - Neon sarı kenarlık
   - Dalgalanan ışık efekti

2. Animasyonlar:
   - Hover durumunda roket sallanma efekti
   - Sürekli dalgalanan dış çember
   - Yumuşak görünme/kaybolma geçişleri
   - Yumuşak yukarı kaydırma

3. Davranış:
   - 20px scroll sonrası görünür
   - Sayfa başına dönünce kaybolur
   - Tıklandığında yumuşak scroll