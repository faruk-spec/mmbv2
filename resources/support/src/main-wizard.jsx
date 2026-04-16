import React from 'react';
import { createRoot } from 'react-dom/client';
import CreateTicketPage from './pages/CreateTicketPage.jsx';

const container = document.getElementById('support-wizard-root');
if (container) {
  const root = createRoot(container);
  root.render(<CreateTicketPage />);
}
