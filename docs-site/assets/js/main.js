// Laravel Bitwise Permission docs — shared interactions

document.addEventListener('DOMContentLoaded', () => {
  // Syntax highlighting
  if (window.hljs) {
    document.querySelectorAll('pre code').forEach((block) => {
      hljs.highlightElement(block);
    });
  }

  // Copy-to-clipboard on every code block
  document.querySelectorAll('pre').forEach((pre) => {
    if (pre.closest('.no-copy')) return;
    const wrap = document.createElement('div');
    wrap.className = 'code-wrap';
    pre.parentNode.insertBefore(wrap, pre);
    wrap.appendChild(pre);

    const btn = document.createElement('button');
    btn.className = 'copy-btn';
    btn.type = 'button';
    btn.textContent = 'Copy';
    btn.addEventListener('click', () => {
      const text = pre.innerText;
      navigator.clipboard.writeText(text).then(() => {
        btn.textContent = 'Copied';
        btn.classList.add('copied');
        setTimeout(() => {
          btn.textContent = 'Copy';
          btn.classList.remove('copied');
        }, 1600);
      });
    });
    wrap.appendChild(btn);
  });

  // Install strip copy
  document.querySelectorAll('[data-copy]').forEach((el) => {
    el.addEventListener('click', () => {
      navigator.clipboard.writeText(el.getAttribute('data-copy'));
      const original = el.querySelector('.copy-label');
      if (original) {
        const prev = original.textContent;
        original.textContent = 'Copied ✓';
        setTimeout(() => (original.textContent = prev), 1600);
      }
    });
  });

  // Mobile nav toggle
  const toggle = document.querySelector('.nav-toggle');
  const links = document.querySelector('.nav-links');
  if (toggle && links) {
    toggle.addEventListener('click', () => {
      links.classList.toggle('open');
    });
  }
});
