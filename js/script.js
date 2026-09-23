document.addEventListener('DOMContentLoaded', () => {
    const reveals = document.querySelectorAll('.reveal');

    const revealOnScroll = () => {
        const windowHeight = window.innerHeight;
        const elementVisible = 30;

        reveals.forEach((reveal) => {
            const elementTop = reveal.getBoundingClientRect().top;

            if (elementTop < windowHeight - elementVisible) {
                reveal.classList.add('active');
            }
        });
    }

    window.addEventListener('scroll', revealOnScroll);
    revealOnScroll();

    /* --- Random Projects Logic (Home Page) --- */
    const randomGrid = document.getElementById('random-project-grid');
    if (randomGrid) {
        const cards = Array.from(randomGrid.querySelectorAll('.project-card'));
        if (cards.length > 2) {
            let lastPair = [];
            try {
                const stored = sessionStorage.getItem('last_random_projects');
                if (stored) lastPair = JSON.parse(stored);
            } catch (e) {}

            const getCardId = (card) => card.getAttribute('href') || card.querySelector('.p-title')?.textContent?.trim() || '';

            const shuffle = (array) => {
                const arr = [...array];
                for (let i = arr.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [arr[i], arr[j]] = [arr[j], arr[i]];
                }
                return arr;
            };

            let selected = [];
            for (let attempt = 0; attempt < 10; attempt++) {
                const candidate = shuffle(cards).slice(0, 2);
                const candidateIds = candidate.map(getCardId).sort().join(',');
                const lastPairIds = (Array.isArray(lastPair) ? lastPair : []).slice().sort().join(',');

                selected = candidate;
                if (candidateIds !== lastPairIds) {
                    break;
                }
            }

            try {
                sessionStorage.setItem('last_random_projects', JSON.stringify(selected.map(getCardId)));
            } catch (e) {}

            randomGrid.innerHTML = '';
            selected.forEach(card => randomGrid.appendChild(card));
        }
    }

    /* --- Portfolio Filtering Logic --- */
    const filterButtons = document.querySelectorAll('.filter-btn');
    const projectCards = document.querySelectorAll('.project-card');

    if (filterButtons.length > 0) {
        filterButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                // Update active button state
                filterButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const filterValue = btn.getAttribute('data-filter');

                projectCards.forEach(card => {
                    const category = card.getAttribute('data-category');

                    if (filterValue === 'all' || category === filterValue) {
                        card.style.display = 'block'; // Ensure it's visible

                        // Reset animation to ensure it plays again
                        card.style.animation = 'none';
                        card.offsetHeight; /* trigger reflow */
                        card.style.animation = 'fadeInUp 0.5s ease forwards';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }

    /* --- Burger Menu Logic --- */
    const burger = document.querySelector('.burger');
    const nav = document.querySelector('.nav-links');
    const navLinks = document.querySelectorAll('.nav-links a');

    if (burger) {
        burger.addEventListener('click', () => {
            console.log('Burger clicked'); // Debug

            // Toggle Nav
            nav.classList.toggle('nav-active');

            // Burger Animation
            burger.classList.toggle('toggle');
        });

        // Close menu when a link is clicked
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                nav.classList.remove('nav-active');
                burger.classList.remove('toggle');
            });
        });
    }
});
