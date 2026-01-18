// Initialisation AOS
AOS.init({
    duration: 1000,
    once: true,
    offset: 100,
    delay: 100
});

// Variables globales
let isScrolling = false;

// Navigation scroll effect
const navbar = document.querySelector('.navbar');
window.addEventListener('scroll', function() {
    if (!isScrolling) {
        window.requestAnimationFrame(function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
            isScrolling = false;
        });
        isScrolling = true;
    }
});

// Back to Top Button
const backToTopBtn = document.getElementById('backToTop');

function toggleBackToTop() {
    if (window.scrollY > 400) {
        backToTopBtn.classList.add('visible');
    } else {
        backToTopBtn.classList.remove('visible');
    }
}

window.addEventListener('scroll', toggleBackToTop);
toggleBackToTop(); // Vérifier l'état initial

backToTopBtn.addEventListener('click', function(e) {
    e.preventDefault();
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

// Compteurs animés
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-count'));
        const suffix = counter.textContent.includes('%') ? '%' : '';
        let count = 0;
        const increment = target / 100;
        
        const updateCounter = () => {
            if (count < target) {
                count += increment;
                counter.textContent = Math.ceil(count) + suffix;
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target + suffix;
            }
        };
        
        updateCounter();
    });
}

// Observer pour déclencher les compteurs quand ils deviennent visibles
const statsSection = document.querySelector('.hero-stats');
if (statsSection) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounters();
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    observer.observe(statsSection);
}

// Effet de parallaxe léger sur les formes d'arrière-plan
const shapes = document.querySelectorAll('.bg-animated-shapes .shape');
let mouseX = 0;
let mouseY = 0;

window.addEventListener('mousemove', (e) => {
    mouseX = e.clientX / window.innerWidth;
    mouseY = e.clientY / window.innerHeight;
    
    updateShapesPosition();
});

function updateShapesPosition() {
    shapes.forEach((shape, index) => {
        const speed = (index + 1) * 0.5;
        const x = (mouseX - 0.5) * 100 * speed;
        const y = (mouseY - 0.5) * 100 * speed;
        
        shape.style.transform = `translate(${x}px, ${y}px) rotate(${index * 45}deg)`;
    });
}

// Animation de la barre de progression
const progressBar = document.querySelector('.progress-bar');
if (progressBar) {
    let progress = 0;
    const interval = setInterval(() => {
        if (progress >= 85) {
            clearInterval(interval);
        } else {
            progress += 1;
            progressBar.style.width = `${progress}%`;
        }
    }, 20);
}

// Navigation fluide
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        if (targetId.startsWith('#')) {
            e.preventDefault();
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                const navbarHeight = navbar.offsetHeight;
                const targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - navbarHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Mettre à jour le lien actif
                document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
                    link.classList.remove('active');
                });
                this.classList.add('active');
                
                // Fermer le menu mobile s'il est ouvert
                const navbarCollapse = document.querySelector('.navbar-collapse');
                if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                    document.querySelector('.navbar-toggler').click();
                }
            }
        }
    });
});

// Mettre à jour le lien actif au scroll
function updateActiveNavLink() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link[href^="#"]');
    
    let currentSection = '';
    
    sections.forEach(section => {
        const sectionTop = section.offsetTop - 100;
        const sectionHeight = section.clientHeight;
        if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
            currentSection = section.getAttribute('id');
        }
    });
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${currentSection}`) {
            link.classList.add('active');
        }
    });
}

window.addEventListener('scroll', updateActiveNavLink);

// Animation des cartes au scroll
function animateCardsOnScroll() {
    const cards = document.querySelectorAll('.feature-card');
    
    cards.forEach(card => {
        const cardPosition = card.getBoundingClientRect().top;
        const screenPosition = window.innerHeight / 1.3;
        
        if (cardPosition < screenPosition) {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }
    });
}

// Initialiser l'animation des cartes
document.querySelectorAll('.feature-card').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
});

window.addEventListener('scroll', animateCardsOnScroll);
animateCardsOnScroll();

// Gestion des formulaires externes
document.querySelectorAll('a[href^="mailto:"]').forEach(link => {
    link.addEventListener('click', function(e) {
        // Animation de confirmation
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-envelope me-2"></i>Ouverture de votre client mail...';
        setTimeout(() => {
            this.innerHTML = originalText;
        }, 2000);
    });
});

document.querySelectorAll('a[href^="tel:"]').forEach(link => {
    link.addEventListener('click', function(e) {
        // Animation de confirmation
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-phone me-2"></i>Appel en cours...';
        setTimeout(() => {
            this.innerHTML = originalText;
        }, 2000);
    });
});

// Mettre à jour l'année dans le footer
const currentYear = new Date().getFullYear();
document.querySelectorAll('footer p').forEach(p => {
    if (p.textContent.includes('2026')) {
        p.textContent = p.textContent.replace('2026', currentYear);
    }
});

// Détection des appareils mobiles pour optimiser les animations
const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

if (isMobile) {
    // Réduire les animations sur mobile
    document.querySelectorAll('.feature-card').forEach(card => {
        card.style.transition = 'opacity 0.3s ease';
    });
    
    // Désactiver le parallaxe sur mobile
    window.removeEventListener('mousemove', updateShapesPosition);
}

// Animation supplémentaire pour les boutons CTA
document.addEventListener('DOMContentLoaded', function() {
    const ctaButtons = document.querySelectorAll('.pulse');
    
    ctaButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});

// Précharger les images pour améliorer les performances
function preloadImages() {
    const images = [
        'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=800&q=80'
    ];
    
    images.forEach(src => {
        const img = new Image();
        img.src = src;
    });
}

preloadImages();

// Gestion des erreurs
window.addEventListener('error', function(e) {
    console.warn('Erreur détectée:', e.message);
});