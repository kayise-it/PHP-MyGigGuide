// Rotating words animation
document.addEventListener('DOMContentLoaded', function() {
    const rotatingWords = ['Events', 'Artists', 'Concerts', 'Festivals'];
    let currentWordIndex = 0;
    
    // Find the rotating words container
    const container = document.getElementById('rotating-words-container');
    if (!container) return;
    
    // Create word spans
    const wordSpans = rotatingWords.map((word, index) => {
        const span = document.createElement('span');
        span.textContent = word;
        span.className = 'absolute top-0 left-0 bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent transition-all duration-1000 ease-in-out transform';
        if (index === 0) {
            span.classList.add('opacity-100', 'translate-y-0', 'scale-100');
        } else {
            span.classList.add('opacity-0', 'translate-y-4', 'scale-95');
        }
        return span;
    });
    
    // Add spans to container
    wordSpans.forEach(span => container.appendChild(span));
    
    // Rotate words every 5 seconds
    setInterval(() => {
        // Hide current word
        wordSpans[currentWordIndex].classList.remove('opacity-100', 'translate-y-0', 'scale-100');
        wordSpans[currentWordIndex].classList.add('opacity-0', '-translate-y-4', 'scale-95');
        
        // Move to next word
        currentWordIndex = (currentWordIndex + 1) % rotatingWords.length;
        
        // Show next word
        wordSpans[currentWordIndex].classList.remove('opacity-0', 'translate-y-4', 'scale-95');
        wordSpans[currentWordIndex].classList.add('opacity-100', 'translate-y-0', 'scale-100');
    }, 5000);
});
