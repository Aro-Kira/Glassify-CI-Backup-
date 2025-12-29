class Carousel {
    constructor() {
        this.carouselTrack = document.querySelector('.carousel-track');
        this.slides = document.querySelectorAll('.carousel-slide');
        this.prevBtn = document.querySelector('.carousel-btn-prev');
        this.nextBtn = document.querySelector('.carousel-btn-next');
        this.indicators = document.querySelectorAll('.indicator');
        this.currentIndex = 1; // Start with second slide as center
        this.slideCount = this.slides.length;
        this.autoSlideInterval = null;
        
        this.init();
    }
    
    init() {
        // Event listeners
        this.prevBtn.addEventListener('click', () => this.prevSlide());
        this.nextBtn.addEventListener('click', () => this.nextSlide());
        
        this.indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => this.goToSlide(index));
        });
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') this.prevSlide();
            if (e.key === 'ArrowRight') this.nextSlide();
        });
        
        this.startAutoSlide();
        
        this.carouselTrack.parentElement.addEventListener('mouseenter', () => {
            this.stopAutoSlide();
        });
        
        this.carouselTrack.parentElement.addEventListener('mouseleave', () => {
            this.startAutoSlide();
        });
        
        this.enableSwipe();
        this.updateCarousel();
        
        // Update carousel on window resize
        window.addEventListener('resize', () => {
            this.updateCarousel();
        });
    }
    
    updateCarousel() {
        // RESPONSIVE CALCULATION
        let slideWidth, visibleSlides, offset;
        
        // Get current screen width
        const screenWidth = window.innerWidth;
        
        if (screenWidth <= 768) {
            // Mobile: 1 slide visible
            slideWidth = 100;
            visibleSlides = 1;
            offset = 0;
        } else if (screenWidth <= 1024) {
            // Tablet: 2 slides visible
            slideWidth = 50;
            visibleSlides = 2;
            offset = 0.5;
        } else {
            // Desktop: 3 slides visible
            slideWidth = 33.33;
            visibleSlides = 3;
            offset = 1;
        }
        
        // Calculate the offset to center the active slide
        const transformValue = (this.currentIndex - offset) * -slideWidth;
        
        this.carouselTrack.style.transform = `translateX(${transformValue}%)`;
        
        // Update active states
        this.indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === this.currentIndex);
        });
        
        this.slides.forEach((slide, index) => {
            slide.classList.toggle('active', index === this.currentIndex);
        });
    }
    
    nextSlide() {
        // Allow sliding until the last slide can be centered
        if (this.currentIndex < this.slideCount - 1) {
            this.currentIndex++;
        } else {
            // If at the end, you can choose to loop or stop
            this.currentIndex = 0; // Loop back to beginning (optional)
        }
        this.updateCarousel();
    }
    
    prevSlide() {
        // Allow sliding until the first slide can be centered
        if (this.currentIndex > 0) {
            this.currentIndex--;
        } else {
            // If at the beginning, you can choose to loop or stop
            this.currentIndex = this.slideCount - 1; // Loop to end (optional)
        }
        this.updateCarousel();
    }
    
    goToSlide(index) {
        // Responsive slide navigation
        const screenWidth = window.innerWidth;
        
        if (screenWidth <= 768) {
            // Mobile: allow any slide
            this.currentIndex = index;
        } else if (screenWidth <= 1024) {
            // Tablet: allow slides that can be properly displayed
            if (index >= 0 && index <= this.slideCount - 1) {
                this.currentIndex = index;
            }
        } else {
            // Desktop: ensure the index can be properly centered
            if (index >= 1 && index <= this.slideCount - 2) {
                this.currentIndex = index;
            } else if (index === 0) {
                this.currentIndex = 1;
            } else if (index === this.slideCount - 1) {
                this.currentIndex = this.slideCount - 2;
            }
        }
        this.updateCarousel();
    }
    
    startAutoSlide() {
        this.autoSlideInterval = setInterval(() => {
            this.nextSlide();
        }, 5000);
    }
    
    stopAutoSlide() {
        if (this.autoSlideInterval) {
            clearInterval(this.autoSlideInterval);
            this.autoSlideInterval = null;
        }
    }
    
    enableSwipe() {
        let startX = 0;
        let endX = 0;
        const minSwipeDistance = 50;
        
        this.carouselTrack.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
        });
        
        this.carouselTrack.addEventListener('touchend', (e) => {
            endX = e.changedTouches[0].clientX;
            this.handleSwipe(startX, endX, minSwipeDistance);
        });
        
        this.carouselTrack.addEventListener('mousedown', (e) => {
            startX = e.clientX;
            document.addEventListener('mouseup', handleMouseUp);
        });
        
        const handleMouseUp = (e) => {
            endX = e.clientX;
            this.handleSwipe(startX, endX, minSwipeDistance);
            document.removeEventListener('mouseup', handleMouseUp);
        };
    }
    
    handleSwipe(startX, endX, minSwipeDistance) {
        const swipeDistance = Math.abs(endX - startX);
        
        if (swipeDistance > minSwipeDistance) {
            if (endX < startX) {
                this.nextSlide();
            } else {
                this.prevSlide();
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new Carousel();
});