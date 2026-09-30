(function () {
    const storageKey = 'labflow-site-theme';
    const savedTheme = localStorage.getItem(storageKey);
    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    document.body.dataset.theme = savedTheme || systemTheme;
    document.querySelectorAll('[data-theme-toggle] i').forEach((icon) => {
        icon.className = document.body.dataset.theme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    });

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const nextTheme = document.body.dataset.theme === 'dark' ? 'light' : 'dark';
            document.body.dataset.theme = nextTheme;
            localStorage.setItem(storageKey, nextTheme);
            const icon = button.querySelector('i');
            if (icon) icon.className = nextTheme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        });
    });

    const campaignSelect = document.getElementById('campaign-id');
    const branchSelect = document.getElementById('branch-id');
    const branchData = document.getElementById('campaign-branch-data');
    if (campaignSelect && branchSelect && branchData) {
        let options = {};
        try { options = JSON.parse(branchData.textContent || '{}'); } catch (_) { options = {}; }
        const placeholder = branchSelect.options[0]?.textContent || '';
        const renderBranches = (selected = '') => {
            branchSelect.replaceChildren(new Option(placeholder, ''));
            (options[campaignSelect.value] || []).forEach((branch) => {
                branchSelect.add(new Option(branch.name, branch.id, false, String(branch.id) === String(selected)));
            });
            branchSelect.disabled = !campaignSelect.value || !(options[campaignSelect.value] || []).length;
        };
        campaignSelect.addEventListener('change', () => renderBranches(''));
        renderBranches(branchSelect.dataset.selectedBranch || branchSelect.value);
    }

    document.querySelectorAll('.needs-validation').forEach((form) => form.addEventListener('submit', (event) => {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    }));

    if (window.jQuery) {
        window.jQuery(() => window.jQuery('.form-control, .form-select').on('input change', function () {
            if (this.checkValidity()) this.classList.remove('is-invalid');
        }));
    }
})();
