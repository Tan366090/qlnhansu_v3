// Gamification module
class Gamification {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadUserProgress();
    }

    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            // Add event listeners for gamification features
        });
    }

    loadUserProgress() {
        fetch('/api/user-progress')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updateProgressUI(data.data);
                }
            })
            .catch(error => {
                console.error('Error loading user progress:', error);
            });
    }

    updateProgressUI(progress) {
        // Update UI with user progress
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach(bar => {
            const type = bar.dataset.type;
            if (progress[type]) {
                bar.style.width = `${progress[type]}%`;
                bar.textContent = `${progress[type]}%`;
            }
        });

        // Update badges
        const badgesContainer = document.querySelector('.badges-container');
        if (badgesContainer) {
            badgesContainer.innerHTML = progress.badges
                .map(badge => `
                    <div class="badge ${badge.earned ? 'earned' : ''}">
                        <img src="${badge.icon}" alt="${badge.name}">
                        <span>${badge.name}</span>
                    </div>
                `)
                .join('');
        }
    }

    awardPoints(points, reason) {
        fetch('/api/award-points', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ points, reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showAchievement(data.achievement);
            }
        })
        .catch(error => {
            console.error('Error awarding points:', error);
        });
    }

    showAchievement(achievement) {
        const achievementPopup = document.createElement('div');
        achievementPopup.className = 'achievement-popup';
        achievementPopup.innerHTML = `
            <div class="achievement-content">
                <img src="${achievement.icon}" alt="${achievement.name}">
                <h3>${achievement.name}</h3>
                <p>${achievement.description}</p>
            </div>
        `;
        document.body.appendChild(achievementPopup);

        setTimeout(() => {
            achievementPopup.remove();
        }, 5000);
    }
}

// Initialize gamification functionality
const gamification = new Gamification(); 