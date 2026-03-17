document.addEventListener("DOMContentLoaded", function () {
    const carousel = document.querySelector("[data-carousel]");
    if (!carousel) return;

    const slides = carousel.querySelectorAll(".slide");
    const thumbs = document.querySelectorAll(".thumb");
    const prevBtn = carousel.querySelector("[data-prev]");
    const nextBtn = carousel.querySelector("[data-next]");

    let currentIndex = 0;

    function showSlide(index) {
        if (!slides.length) return;

        if (index < 0) index = slides.length - 1;
        if (index >= slides.length) index = 0;

        slides.forEach((slide, i) => {
            slide.classList.toggle("active", i === index);
        });

        thumbs.forEach((thumb, i) => {
            thumb.classList.toggle("active", i === index);
        });

        currentIndex = index;
    }

    if (prevBtn) {
        prevBtn.addEventListener("click", function () {
            showSlide(currentIndex - 1);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener("click", function () {
            showSlide(currentIndex + 1);
        });
    }

    thumbs.forEach((thumb, i) => {
        thumb.addEventListener("click", function () {
            showSlide(i);
        });
    });

    document.addEventListener("keydown", function (event) {
        if (event.key === "ArrowLeft") showSlide(currentIndex - 1);
        if (event.key === "ArrowRight") showSlide(currentIndex + 1);
    });

    showSlide(0);
});
