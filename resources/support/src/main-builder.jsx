import React from 'react';
import { createRoot } from 'react-dom/client';
import TemplateBuilderPage from './pages/TemplateBuilderPage.jsx';

const container = document.getElementById('support-builder-root');
if (container) {
  const root = createRoot(container);
  root.render(<TemplateBuilderPage />);
}
