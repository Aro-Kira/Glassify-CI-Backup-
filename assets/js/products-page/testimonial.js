  const testimonials = document.querySelectorAll('.testimonial-text');
  const prevBtn = document.querySelector('.testimonial-arrow.left');
  const testimonialNextBtn = document.querySelector('.testimonial-arrow.right'); // Renamed to avoid conflict
  let currentIndex = 0;

  function showTestimonial(index) {
    testimonials.forEach((t, i) => {
      t.classList.toggle('active', i === index);
    });
  }

  if (testimonialNextBtn) {
    testimonialNextBtn.addEventListener('click', () => {
      currentIndex = (currentIndex + 1) % testimonials.length;
      showTestimonial(currentIndex);
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', () => {
      currentIndex = (currentIndex - 1 + testimonials.length) % testimonials.length;
      showTestimonial(currentIndex);
    });
  }