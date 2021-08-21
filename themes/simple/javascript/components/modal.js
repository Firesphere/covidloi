if (!document.getElementsByClassName('GenericPage').length) {
    document.addEventListener('click', function (e) {
        let target = e.target;

        if (target.hasAttribute('data-toggle') && target.getAttribute('data-toggle') === 'modal') {
            if (target.hasAttribute('data-target')) {
                let m_ID = target.getAttribute('data-target');
                let content = pagecontent[m_ID];
                document.getElementById('modal-title').innerHTML = content.Title;
                document.getElementById('modal-content').innerHTML = content.Content;
                document.getElementById('modal-item').classList.add('open');
                // document.getElementById()
                e.preventDefault();
            }
        }

        // Close modal window with 'data-dismiss' attribute or when the backdrop is clicked
        if ((target.hasAttribute('data-dismiss') && target.getAttribute('data-dismiss') === 'modal') || target.classList.contains('modal')) {
            let modal = document.querySelector('[class="modal open"]');
            modal.classList.remove('open');
            e.preventDefault();
        }
    }, false);
}
