document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });

    const selectAll = document.getElementById('select-all');
    const checkboxes = [...document.querySelectorAll('input[form="bulk-delete"]')];
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            checkboxes.forEach((checkbox) => { checkbox.checked = selectAll.checked; });
        });
        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', () => {
            const count = checkboxes.filter((item) => item.checked).length;
            selectAll.checked = count === checkboxes.length;
            selectAll.indeterminate = count > 0 && count < checkboxes.length;
        }));
    }
});
