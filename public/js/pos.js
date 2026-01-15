document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const input = document.querySelector('input[name="code_search"]');
        if (input) {
            input.focus();
        }
    }, 200);
});

document.addEventListener('livewire:navigated', function() {
    setTimeout(() => {
        const input = document.querySelector('input[name="code_search"]');
        if (input) {
            input.focus();
        }
    }, 100);
});

