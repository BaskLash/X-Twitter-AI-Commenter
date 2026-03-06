(function () {
  // SVG Icons (Gleich geblieben)
  const ICONS = {
    prompt: `<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"></path></svg>`,
    templates: `<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M7 5V3c0-1.1.9-2 2-2h6c1.1 0 2 .9 2 2v2h5v2H2V5h5zm2-2v2h6V3H9zM2 9h20v10c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V9zm16 4h-4v-4h-2v4H8v2h4v4h2v-4h4v-2z"></path></svg>`,
    aiReply: `<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 2c-5.52 0-10 4.48-10 10 0 1.94.55 3.75 1.5 5.28l-1.5 5.72 5.72-1.5c1.53.95 3.34 1.5 5.28 1.5 5.52 0 10-4.48 10-10S17.52 2 12 2zm1.2 13.5l-2.4-2.4-2.4 2.4-1.4-1.4 2.4-2.4-2.4-2.4 1.4-1.4 2.4 2.4 2.4-2.4 1.4 1.4-2.4 2.4 2.4 2.4-1.4 1.4z"></path></svg>`,
    regenerate: `<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8zm-6 8c0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3c-3.31 0-6-2.69-6-6z"></path></svg>`
  };

  // Deine gewünschte Funktion zum Einfügen des Textes
  function insertTextIntoReply(text) {
    const editor = document.querySelector('div[data-testid="tweetTextarea_0"][contenteditable="true"]');
    if (!editor) return;

    editor.focus();
    document.execCommand("insertText", false, text);
    editor.dispatchEvent(new InputEvent("input", { bubbles: true }));
  }

  function createCustomToolbar() {
    const toolbar = document.createElement("div");
    toolbar.className = "my-custom-x-toolbar";
    
    Object.assign(toolbar.style, {
      display: "flex",
      gap: "4px",
      padding: "8px 12px",
      borderTop: "1px solid rgb(47, 51, 54)",
      backgroundColor: "transparent"
    });

    const buttons = [
      { id: 'prompt', label: 'Prompt', icon: ICONS.prompt, text: "[AI Prompt: ] " },
      { id: 'templates', label: 'Templates', icon: ICONS.templates, text: "Schau dir mal diese Vorlage an: " },
      { id: 'ai-reply', label: 'AI Reply', icon: ICONS.aiReply, text: "Das ist eine KI-generierte Antwort. " },
      { id: 'regenerate', label: 'Regenerate', icon: ICONS.regenerate, text: "(Neu generiert) " }
    ];

    buttons.forEach(data => {
      const btn = document.createElement("button");
      btn.className = "my-x-btn";
      btn.innerHTML = `${data.icon} <span style="font-weight: 700; font-size: 14px; margin-left: 4px;">${data.label}</span>`;
      
      Object.assign(btn.style, {
        background: "transparent",
        color: "rgb(29, 155, 240)",
        border: "none",
        borderRadius: "9999px",
        padding: "0 12px",
        height: "34px",
        cursor: "pointer",
        display: "flex",
        alignItems: "center",
        transition: "background-color 0.2s",
        fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif'
      });

      btn.onmouseover = () => btn.style.backgroundColor = "rgba(29, 155, 240, 0.1)";
      btn.onmouseout = () => btn.style.backgroundColor = "transparent";
      
      // Klick-Event nutzt jetzt deine insert-Funktion
      btn.onclick = (e) => {
        e.preventDefault();
        insertTextIntoReply(data.text);
      };

      toolbar.appendChild(btn);
    });

    return toolbar;
  }

  function injectToolbar() {
    const replyToolbars = document.querySelectorAll('div[data-testid="toolBar"]');
    replyToolbars.forEach(toolbar => {
      const container = toolbar.closest('div[data-testid="cellInnerDiv"]') || toolbar.parentElement;
      if (container.querySelector(".my-custom-x-toolbar")) return;

      const customToolbar = createCustomToolbar();
      toolbar.insertAdjacentElement('afterend', customToolbar);
    });
  }

  const observer = new MutationObserver(() => injectToolbar());
  observer.observe(document.body, { childList: true, subtree: true });

  injectToolbar();
})();
