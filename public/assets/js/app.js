(function () {
  const form = document.querySelector('[data-live-pages]');
  if (!form) return;

  const textArea = form.querySelector('[data-word-source]');
  const imageInput = form.querySelector('[data-image-source]');
  const titleInput = form.querySelector('[data-title-source]');
  const wordOut = form.querySelector('[data-word-count]');
  const pageOut = form.querySelector('[data-page-count]');

  const countWords = (text) => {
    const matches = text.trim().match(/\b[\p{L}\p{N}_-]+\b/gu);
    return matches ? matches.length : 0;
  };

  const refresh = () => {
    const words = countWords(textArea.value || '');
    const images = parseInt(imageInput.value || '0', 10) || 0;
    const title = !!titleInput.checked;
    const pages = Math.ceil((words / 900) + (images * 0.15) + (title ? 0.5 : 0));

    wordOut.textContent = String(words);
    pageOut.textContent = String(pages);
  };

  ['input', 'change', 'keyup'].forEach((eventName) => {
    form.addEventListener(eventName, refresh);
  });

  refresh();
})();
