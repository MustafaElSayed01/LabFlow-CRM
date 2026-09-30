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

    const campaignInput = document.getElementById('selected-campaign-id');
    const campaignTags = document.querySelectorAll('[data-campaign-id]');
    const branchSelect = document.getElementById('branch-select');
    const branchPicker = document.getElementById('branch-picker');
    const singleBranchInput = document.getElementById('single-branch-id');
    const branchData = document.getElementById('campaign-branch-data');
    const campaignFeedback = document.querySelector('.campaign-validation');
    if (campaignInput && branchSelect && branchPicker && singleBranchInput && branchData) {
        let options = {};
        try { options = JSON.parse(branchData.textContent || '{}'); } catch (_) { options = {}; }
        const placeholder = branchSelect.options[0]?.textContent || '';
        const renderBranches = (campaignId, selected = '') => {
            const branches = options[campaignId] || [];
            branchSelect.replaceChildren(new Option(placeholder, ''));
            branches.forEach((branch) => {
                branchSelect.add(new Option(branch.name, branch.id, false, String(branch.id) === String(selected)));
            });
            branchPicker.hidden = branches.length < 2;
            branchSelect.disabled = branches.length < 2;
            branchSelect.required = branches.length > 1;
            if (branches.length > 1) {
                branchSelect.name = 'branch_id';
                singleBranchInput.removeAttribute('name');
                singleBranchInput.value = '';
            } else {
                branchSelect.removeAttribute('name');
                singleBranchInput.value = branches.length === 1 ? String(branches[0].id) : '';
                if (branches.length === 1) singleBranchInput.name = 'branch_id';
                else singleBranchInput.removeAttribute('name');
            }
        };
        const selectCampaign = (campaignId, selectedBranch = '') => {
            campaignInput.value = String(campaignId || '');
            campaignTags.forEach((tag) => {
                const selected = tag.dataset.campaignId === String(campaignId);
                tag.classList.toggle('is-selected', selected);
                tag.setAttribute('aria-pressed', selected ? 'true' : 'false');
            });
            if (campaignFeedback) campaignFeedback.classList.remove('is-visible');
            renderBranches(String(campaignId || ''), selectedBranch);
        };
        campaignTags.forEach((tag) => tag.addEventListener('click', () => selectCampaign(tag.dataset.campaignId)));
        document.querySelectorAll('[data-campaign-select]').forEach((link) => {
            link.addEventListener('click', () => selectCampaign(link.dataset.campaignSelect));
        });
        selectCampaign(campaignInput.value, branchSelect.dataset.selectedBranch || '');

        const bookingForm = document.querySelector('#booking form');
        if (bookingForm) bookingForm.addEventListener('submit', (event) => {
            if (!campaignInput.value) {
                event.preventDefault();
                event.stopPropagation();
                if (campaignFeedback) campaignFeedback.classList.add('is-visible');
                campaignTags[0]?.focus();
            }
        }, true);
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
