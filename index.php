<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>TV App</title>

<style>
body {
    margin: 0;
    background: #0f0f0f;
    font-family: Arial;
    color: white;
    overflow: hidden;
}

/* ===== GRID ===== */
#mainScreen {
    display: block;
}

.container {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 20px;
    padding: 30px;
    height: 100vh;
}

.card {
    outline: none;
    transition: transform 0.2s ease;
}

/* Poster lazy */
.poster {
    width: 100%;
    aspect-ratio: 3 / 2;
    object-fit: cover;
    border-radius: 10px;
    background: #222;

    opacity: 0;
    transition: opacity 0.3s ease;
}

.poster.loaded {
    opacity: 1;
}

.info {
    margin-top: 8px;
}

.title {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 4px;

    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.desc {
    font-size: 12px;
    color: #aaa;

    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Focus */
.card:focus {
    transform: scale(1.08);
    z-index: 10;
}

.card:focus .poster {
    border: 3px solid white;
    box-shadow: 0 0 20px rgba(255,255,255,0.7);
}

/* ===== DETAIL ===== */
#detailScreen {
    display: none;
    padding: 40px;
}

#btnBack {
    padding: 10px 20px;
    font-size: 16px;
}

#btnBack:focus {
    outline: 2px solid white;
}

.detail-content {
    display: flex;
    gap: 40px;
    margin-top: 30px;
}

.detail-content img {
    width: 400px;
    border-radius: 12px;
}

.detail-info {
    max-width: 600px;
}
</style>
</head>

<body>

<!-- ===== HOME ===== -->
<div id="mainScreen">
    <div class="container" id="grid"></div>
</div>

<!-- ===== DETAIL ===== -->
<div id="detailScreen">
    <button id="btnBack" tabindex="0">← Quay lại</button>

    <div class="detail-content">
        <img id="detailImg">
        <div class="detail-info">
            <h2 id="detailTitle"></h2>
            <p id="detailDesc"></p>
        </div>
    </div>
</div>

<script>
const grid = document.getElementById('grid');
const total = 50;
const col = 5;

const movies = Array.from({ length: total }, (_, i) => ({
    title: "Phim " + (i + 1),
    desc: "Đây là mô tả chi tiết của phim " + (i + 1),
    img: "https://picsum.photos/300/200?" + i
}));

// ===== RENDER GRID (lazy) =====
movies.forEach((m, i) => {
    const item = document.createElement('div');
    item.className = 'card';
    item.setAttribute('tabindex', '0');

    item.innerHTML = `
        <img class="poster lazy" 
             data-src="${m.img}" 
             src="https://via.placeholder.com/300x200?text=Loading">
        <div class="info">
            <div class="title">${m.title}</div>
            <div class="desc">${m.desc}</div>
        </div>
    `;

    grid.appendChild(item);
});

const cards = document.querySelectorAll('.card');
let currentIndex = 0;
let isDetail = false;

/* ===== LAZY LOAD ===== */
const lazyImages = document.querySelectorAll('.lazy');

const observer = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target;

            img.src = img.dataset.src;

            img.onload = () => {
                img.classList.add('loaded');
            };

            obs.unobserve(img);
        }
    });
}, {
    root: null,
    rootMargin: "200px",
    threshold: 0.1
});

lazyImages.forEach(img => observer.observe(img));

/* ===== PRELOAD NEAR FOCUS ===== */
function preloadNearby(index) {
    for (let i = index - 5; i <= index + 5; i++) {
        if (i >= 0 && i < cards.length) {
            const img = cards[i].querySelector('.poster');
            if (img && img.dataset.src && !img.src.includes(img.dataset.src)) {
                img.src = img.dataset.src;
                img.classList.add('loaded');
            }
        }
    }
}

/* ===== FOCUS ===== */
function updateFocus() {
    const el = cards[currentIndex];
    el.focus();

    el.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
    });

    preloadNearby(currentIndex);
}

window.onload = () => updateFocus();

/* ===== NAVIGATION ===== */
function handleMove(key) {
    switch (key) {
        case 'ArrowRight':
            if ((currentIndex % col) < col - 1 && currentIndex < cards.length - 1) {
                currentIndex++;
            }
            break;

        case 'ArrowLeft':
            if ((currentIndex % col) > 0) {
                currentIndex--;
            }
            break;

        case 'ArrowDown':
            if (currentIndex + col < cards.length) {
                currentIndex += col;
            }
            break;

        case 'ArrowUp':
            if (currentIndex - col >= 0) {
                currentIndex -= col;
            }
            break;
    }

    updateFocus();
}

/* ===== DETAIL ===== */
function openDetail(index) {
    const movie = movies[index];

    document.getElementById('detailImg').src = movie.img;
    document.getElementById('detailTitle').innerText = movie.title;
    document.getElementById('detailDesc').innerText = movie.desc;

    document.getElementById('mainScreen').style.display = 'none';
    document.getElementById('detailScreen').style.display = 'block';

    isDetail = true;

    setTimeout(() => {
        document.getElementById('btnBack').focus();
    }, 100);
}

function goBack() {
    document.getElementById('detailScreen').style.display = 'none';
    document.getElementById('mainScreen').style.display = 'block';

    isDetail = false;

    updateFocus();
}

document.getElementById('btnBack').addEventListener('click', goBack);

/* ===== KEY ===== */
document.addEventListener('keydown', (e) => {

    if (isDetail) {
        if (e.key === 'Backspace' || e.key === 'Escape') {
            goBack();
        }
        return;
    }

    switch (e.key) {
        case 'Enter':
            openDetail(currentIndex);
            break;

        case 'ArrowRight':
        case 'ArrowLeft':
        case 'ArrowUp':
        case 'ArrowDown':
            handleMove(e.key);
            break;
    }
});
</script>

</body>
</html>
