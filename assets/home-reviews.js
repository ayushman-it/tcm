(function () {
    const reviews = {
        primary: [
            {
                name: 'Disha Budhrani',
                course: 'Web Development',
                rating: 10,
                quote: 'My doubts are cleared properly, and the concepts are easy to understand. The instructor teaches very well.'
            },
            {
                name: 'Jayesh Ghalekar',
                course: 'Web Development',
                rating: 10,
                quote: 'TCM ki practical teaching, easy-to-understand classes, supportive mentors aur real projects sabse achhe lage.'
            },
            {
                name: 'Jyoti Soni',
                course: 'Web Development',
                rating: 9,
                quote: 'Sir answers every question and helps with any issues that arise, while ensuring that we can handle things ourselves.'
            }
        ],
        secondary: [
            {
                name: 'Sakshi Somnath Umbare',
                course: 'Web Development',
                rating: 9,
                quote: 'TCM provides opportunities for personal growth and skill development. It helps improve communication, leadership, and confidence.'
            },
            {
                name: 'Pratham Soni',
                course: 'Web Development',
                rating: 10,
                quote: 'Jab tak doubt clear nhi hota next class me nhi jate.'
            },
            {
                name: 'Harshit Nagpure',
                course: 'Web Development',
                rating: 10,
                quote: 'Guidance and friendly environment.'
            }
        ]
    };

    const escapeHtml = (value) => value.replace(/[&<>'"]/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#39;',
        '"': '&quot;'
    }[character]));

    const initials = (name) => name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase();

    const createCard = (review, duplicate) => `
        <article class="review-card" role="listitem"${duplicate ? ' aria-hidden="true"' : ''}>
            <div class="review-card-top">
                <div class="review-badge">
                    <i class="bi bi-patch-check-fill text-danger" aria-hidden="true"></i>
                    <span>Verified Learner</span>
                </div>
                <div class="review-stars" aria-label="5 out of 5 stars">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                </div>
            </div>
            <blockquote>&ldquo;${escapeHtml(review.quote)}&rdquo;</blockquote>
            <div class="review-user">
                <div class="avatar" aria-hidden="true">${initials(review.name)}</div>
                <div>
                    <h5>${escapeHtml(review.name)}</h5>
                    <span>${escapeHtml(review.course)}</span>
                </div>
            </div>
        </article>`;

    Object.entries(reviews).forEach(([key, list]) => {
        const track = document.querySelector(`[data-review-track="${key}"]`);
        if (!track) return;

        track.innerHTML = [...list, ...list]
            .map((review, index) => createCard(review, index >= list.length))
            .join('');
    });
})();
