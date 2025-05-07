class NotificationSounds {
    static #sounds = {
        success: new Audio('/assets/sounds/success.mp3'),
        error: new Audio('/assets/sounds/error.mp3'),
        warning: new Audio('/assets/sounds/warning.mp3'),
        info: new Audio('/assets/sounds/info.mp3')
    };

    static #isEnabled = true;
    static #volume = 0.5;

    static init() {
        this.#loadSettings();
        this.#setupAudio();
    }

    static #loadSettings() {
        const settings = localStorage.getItem('notificationSoundSettings');
        if (settings) {
            const { enabled, volume } = JSON.parse(settings);
            this.#isEnabled = enabled;
            this.#volume = volume;
            this.#updateVolume();
        }
    }

    static #setupAudio() {
        Object.values(this.#sounds).forEach(sound => {
            sound.volume = this.#volume;
        });
    }

    static #updateVolume() {
        Object.values(this.#sounds).forEach(sound => {
            sound.volume = this.#volume;
        });
    }

    static play(type) {
        if (!this.#isEnabled) return;

        const sound = this.#sounds[type];
        if (sound) {
            sound.currentTime = 0;
            sound.play().catch(() => {
                // Ignore errors if sound can't play
            });
        }
    }

    static setEnabled(enabled) {
        this.#isEnabled = enabled;
        this.#saveSettings();
    }

    static setVolume(volume) {
        this.#volume = Math.max(0, Math.min(1, volume));
        this.#updateVolume();
        this.#saveSettings();
    }

    static #saveSettings() {
        localStorage.setItem('notificationSoundSettings', JSON.stringify({
            enabled: this.#isEnabled,
            volume: this.#volume
        }));
    }

    static isEnabled() {
        return this.#isEnabled;
    }

    static getVolume() {
        return this.#volume;
    }
}

// Initialize the service when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    NotificationSounds.init();
}); 