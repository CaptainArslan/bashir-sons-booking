// JS
document.querySelectorAll('.video__link').forEach(link => {
  link.addEventListener('click', e => {
    e.preventDefault();
    const url = new URL(link.href);
    const videoId = url.searchParams.get('v');
    if (!videoId) return;

    const iframe = document.createElement('iframe');
    iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
    iframe.width  = link.offsetWidth;
    iframe.height = link.offsetHeight;
    iframe.frameBorder = 0;
    iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
    iframe.allowFullscreen = true;

    link.parentNode.replaceChild(iframe, link);
  });
});




let selectedSeatBtn = null;

document.addEventListener("DOMContentLoaded", function () {
    const genderModal = new bootstrap.Modal(document.getElementById('genderModal'));
    const confirmBtn = document.getElementById('confirmGenderBtn');

    document.querySelectorAll('.seat-btn').forEach(button => {
        button.addEventListener('click', function () {
            if (this.classList.contains('booked-male') || this.classList.contains('booked-female')) {
                this.classList.remove('booked-male', 'booked-female');
                this.classList.add('available');
            } else if (this.classList.contains('available')) {
                selectedSeatBtn = this;
                document.getElementById('genderMale').checked = true; // reset to default
                genderModal.show();
            }
        });
    });

    confirmBtn.addEventListener('click', function () {
        const selectedGender = document.querySelector('input[name="gender"]:checked').value;
        if (selectedSeatBtn) {
            selectedSeatBtn.classList.remove('available');
            selectedSeatBtn.classList.add(selectedGender === 'male' ? 'booked-male' : 'booked-female');
            selectedSeatBtn = null;
        }
        genderModal.hide();
    });
});