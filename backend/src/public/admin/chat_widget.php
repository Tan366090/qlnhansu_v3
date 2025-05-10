<?php include 'headers.php'; ?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
<style>
:root {
    --primary: #6366f1;
    --primary-light: #e0e7ff;
    --primary-dark: #4f46e5;
    --bot-bg: #f8fafc;
    --user-bg: #6366f1;
    --user-color: #fff;
    --suggest-bg: #f1f5f9;
    --suggest-hover: #dbeafe;
    --border-radius: 24px;
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --gradient-primary: linear-gradient(135deg, #6366f1, #4f46e5);
    --gradient-secondary: linear-gradient(135deg, #8b5cf6, #6366f1);
}

/* Updated chat widget size */
.chat-widget-box {
    width: 100%;
    height: 100vh;
    max-width: 1200px;
    margin: 0 auto;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-lg);
    background: #fff;
    display: flex;
    flex-direction: column;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}
.chat-widget-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 200px;
    background: var(--gradient-primary);
    opacity: 0.1;
    z-index: 0;
}
.chat-widget-box::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-0.895 2-2s-0.895-2-2-2-2 0.895-2 2 0.895 2 2 2zM60 91c1.105 0 2-0.895 2-2s-0.895-2-2-2-2 0.895-2 2 0.895 2 2 2zM35 41c1.105 0 2-0.895 2-2s-0.895-2-2-2-2 0.895-2 2 0.895 2 2 2zM12 60c1.105 0 2-0.895 2-2s-0.895-2-2-2-2 0.895-2 2 0.895 2 2 2z' fill='%236366f1' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
    opacity: 0.5;
    z-index: 0;
}

/* Updated header size */
.chat-widget-header {
    background: linear-gradient(90deg, #36d1c4 0%, #5b6eff 50%, #a66cff 100%);
    color: #fff;
    padding: 12px 16px; /* Reduced padding */
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: var(--shadow);
    position: relative;
    z-index: 1;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    font-size: 0.95rem; /* Smaller font size */
}
.chat-widget-header .title {
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.chat-widget-header .status-dot {
    width: 10px;
    height: 10px;
    background: #22c55e;
    border-radius: 50%;
    margin-right: 6px;
    animation: pulse 2s infinite;
    box-shadow: 0 0 8px rgba(34,197,94,0.5);
}
@keyframes pulse {
    0% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(34,197,94,0.7);
    }
    70% {
        transform: scale(1);
        box-shadow: 0 0 0 10px rgba(34,197,94,0);
    }
    100% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(34,197,94,0);
    }
}
.chat-widget-header .close-btn {
    background: rgba(255,255,255,0.1);
    border: none;
    color: #fff;
    font-size: 1.2rem;
    cursor: pointer;
    opacity: 0.8;
    transition: all 0.2s;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.chat-widget-header .close-btn:hover {
    opacity: 1;
    background: rgba(255,255,255,0.2);
    transform: rotate(90deg);
}

/* Updated messages area */
.chat-widget-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px 12px 8px 12px; /* Reduced padding */
    background: url('background_Chat.jpg') center center repeat;
    background-size: cover;
    display: flex;
    flex-direction: column;
    gap: 12px; /* Reduced gap */
    scroll-behavior: smooth;
    position: relative;
    z-index: 1;
    min-height: 0;
}
.chat-widget-messages::-webkit-scrollbar {
    width: 6px;
}
.chat-widget-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
}
.chat-widget-messages::-webkit-scrollbar-thumb {
    background: #c5c5c5;
    border-radius: 3px;
}
.chat-widget-messages::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
.message-row {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    animation: messageSlide 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    margin-bottom: 8px;
}
@keyframes messageSlide {
    from { 
        opacity: 0; 
        transform: translateY(20px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}
.message-row.user {
    flex-direction: row-reverse;
}
.message-bubble {
    padding: 10px 16px; /* Reduced padding */
    font-size: 0.95rem; /* Smaller font size */
    max-width: 100%; /* Increased max-width */
    border-radius: 18px;
    box-shadow: var(--shadow-sm);
    word-break: break-word;
    position: relative;
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
}
.message-row.user .message-bubble {
    background: #3b82f6;
    color: #fff;
    border-bottom-right-radius: 4px;
}
.message-row.bot .message-bubble {
    background: #f3f4f6;
    color: #222;
    border-bottom-left-radius: 4px;
}
.message-avatar {
    width: 32px; /* Reduced size */
    height: 32px; /* Reduced size */
    border-radius: 50%;
    background: var(--gradient-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem; /* Smaller font size */
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
}
.message-avatar::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0));
    border-radius: 50%;
}
.message-row.user .message-avatar {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: #fff;
}

/* Updated suggestion chips */
.suggestion-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px; /* Reduced gap */
    margin: 8px 0; /* Reduced margin */
    padding: 0 40px; /* Reduced padding */
    justify-content: flex-start;
}
.suggestion-chip {
    flex: 0 1 auto;
    min-width: 140px; /* Reduced min-width */
    max-width: 180px;
    background: var(--suggest-bg);
    color: #222;
    padding: 8px 16px; /* Reduced padding */
    border-radius: 12px; /* Reduced border radius */
    font-size: 0.9rem; /* Smaller font size */
    cursor: pointer;
    border: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.suggestion-chip::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--gradient-primary);
    opacity: 0;
    transition: opacity 0.3s ease;
}
.suggestion-chip:hover {
    color: #fff;
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}
.suggestion-chip:hover::before {
    opacity: 1;
}
.suggestion-chip span {
    position: relative;
    z-index: 1;
}
.typing-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 4px 0 0 32px; /* Reduced margin */
    animation: fadeIn 0.3s ease;
    font-size: 0.85rem; /* Smaller font size */
}
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.typing-dot {
    width: 6px; /* Reduced size */
    height: 6px; /* Reduced size */
    background: #a5b4fc;
    border-radius: 50%;
    display: inline-block;
    animation: typing 1.2s infinite;
}
.typing-dot:nth-child(2) { animation-delay: 0.2s; }
.typing-dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes typing {
    0%, 80%, 100% { transform: scale(0.7); opacity: 0.7; }
    40% { transform: scale(1.2); opacity: 1; }
}

/* Updated input area */
.chat-widget-input {
    padding: 12px 16px; /* Reduced padding */
    background: rgba(255, 255, 255, 0.9);
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 8px;
    box-shadow: 0 -4px 6px -1px rgba(0,0,0,0.05);
    position: relative;
    z-index: 1;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    flex-shrink: 0;
}
.chat-widget-input .input-wrapper {
    flex: 1;
    position: relative;
    display: flex;
    align-items: center;
}
.chat-widget-input input {
    flex: 1;
    border-radius: 20px; /* Reduced border radius */
    border: 1px solid #d1d5db;
    padding: 10px 16px; /* Reduced padding */
    padding-right: 40px;
    font-size: 0.95rem; /* Smaller font size */
    outline: none;
    transition: all 0.3s ease;
    background: rgba(249, 250, 251, 0.8);
}
.chat-widget-input input:focus {
    border: 1.5px solid var(--primary);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
    transform: translateY(-1px);
}
.chat-widget-input .input-actions {
    position: absolute;
    right: 10px;
    display: flex;
    gap: 5px;
}
.chat-widget-input .action-btn {
    background: none;
    border: none;
    color: #6b7280;
    font-size: 1.1rem;
    padding: 5px;
    cursor: pointer;
    transition: all 0.2s;
    border-radius: 50%;
}
.chat-widget-input .action-btn:hover {
    color: var(--primary);
    background: rgba(79,70,229,0.1);
}
.chat-widget-input .send-btn {
    background: var(--gradient-primary);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 40px; /* Reduced size */
    height: 40px; /* Reduced size */
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem; /* Smaller icon */
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
}
.chat-widget-input .send-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0));
    opacity: 0;
    transition: opacity 0.3s ease;
}
.chat-widget-input .send-btn:hover {
    transform: scale(1.05) rotate(5deg);
    box-shadow: var(--shadow);
}
.chat-widget-input .send-btn:hover::before {
    opacity: 1;
}
/* Emoji Picker */
.emoji-picker {
    position: absolute;
    bottom: 100%;
    right: 0;
    background: #fff;
    border-radius: 12px;
    box-shadow: var(--shadow-lg);
    padding: 10px;
    display: none;
    grid-template-columns: repeat(8, 1fr);
    gap: 5px;
    margin-bottom: 10px;
    z-index: 1000;
}
.emoji-picker.active {
    display: grid;
    animation: slideUp 0.3s ease;
}
.emoji-item {
    font-size: 1.5rem;
    padding: 5px;
    cursor: pointer;
    text-align: center;
    border-radius: 8px;
    transition: all 0.2s;
}
.emoji-item:hover {
    background: var(--primary-light);
    transform: scale(1.1);
}
/* File Upload */
.file-upload {
    display: none;
}
.file-preview {
    display: flex;
    gap: 8px;
    padding: 8px;
    background: var(--primary-light);
    border-radius: 8px;
    margin: 8px 0;
    align-items: center;
}
.file-preview .file-info {
    flex: 1;
    font-size: 0.9rem;
}
.file-preview .remove-file {
    color: #ef4444;
    cursor: pointer;
    padding: 4px;
    border-radius: 50%;
    transition: all 0.2s;
}
.file-preview .remove-file:hover {
    background: rgba(239,68,68,0.1);
}
/* Message actions - Improved positioning */
.message-actions {
    display: none;
    gap: 6px; /* Reduced gap */
    margin-top: 2px; /* Reduced margin */
    padding: 3px 6px; /* Reduced padding */
    border-radius: 12px;
    background: rgba(0,0,0,0.03);
    width: fit-content;
    opacity: 0;
    transform: translateY(-4px);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 1;
}
.message-row:hover .message-actions {
    display: flex;
    opacity: 1;
    transform: translateY(0);
}
.message-action-btn {
    padding: 4px 8px; /* Reduced padding */
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
    color: #666;
    font-size: 0.85rem; /* Smaller font size */
    transition: all 0.2s;
    background: transparent;
    border: none;
    cursor: pointer;
    white-space: nowrap;
}
.message-action-btn i {
    font-size: 0.9rem;
    transition: transform 0.2s;
}
.message-action-btn:hover {
    background: var(--primary-light);
    color: var(--primary);
}
.message-action-btn:hover i {
    transform: scale(1.1);
}
/* Position actions based on message type */
.message-row {
    position: relative;
    margin-bottom: 8px;
}
.message-row.user {
    flex-direction: row-reverse;
}
.message-row.user .message-actions {
    margin-left: auto;
    margin-right: 40px;
}
.message-row.bot .message-actions {
    margin-left: 40px;
}
/* Message content wrapper */
.message-content {
    display: flex;
    flex-direction: column;
    max-width: 85%;
}
.message-row.user .message-content {
    align-items: flex-end;
}
.message-row.bot .message-content {
    align-items: flex-start;
}
/* Message bubble adjustments */
.message-bubble {
    position: relative;
    margin-bottom: 2px;
}
/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
    .message-actions {
        background: rgba(255,255,255,0.05);
        backdrop-filter: blur(8px);
    }
    
    .message-action-btn {
        color: #999;
    }
    
    .message-action-btn:hover {
        background: rgba(255,255,255,0.1);
        color: #fff;
    }
}
/* Mobile responsiveness */
@media (max-width: 768px) {
    .message-actions {
        padding: 3px 6px;
    }
    
    .message-action-btn {
        padding: 4px 8px;
        font-size: 0.85rem;
    }
    
    .message-row.user .message-actions {
        margin-right: 32px;
    }
    
    .message-row.bot .message-actions {
        margin-left: 32px;
    }
}
/* Code Block */
.code-block {
    background: #1e293b;
    color: #e2e8f0;
    padding: 8px; /* Reduced padding */
    border-radius: 8px;
    font-family: monospace;
    margin: 8px 0;
    position: relative;
    font-size: 0.9rem; /* Smaller font size */
}
.code-block .copy-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(255,255,255,0.1);
    border: none;
    color: #e2e8f0;
    padding: 3px 6px; /* Reduced padding */
    border-radius: 4px;
    font-size: 0.75rem; /* Smaller font size */
    cursor: pointer;
    transition: all 0.2s;
}
.code-block .copy-btn:hover {
    background: rgba(255,255,255,0.2);
}
/* Message Status */
.message-status {
    font-size: 0.7rem; /* Smaller font size */
    color: #6b7280;
    margin-top: 2px; /* Reduced margin */
    display: flex;
    align-items: center;
    gap: 4px;
}
.message-status i {
    font-size: 0.8rem;
    color: #22c55e;
}
/* Animations */
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
@media (max-width: 768px) {
    .chat-widget-box {
        border-radius: 0;
    }
    
    .chat-widget-header {
        padding: 10px 14px;
    }
    
    .chat-widget-messages {
        padding: 12px 10px 6px 10px;
    }
    
    .chat-widget-input {
        padding: 10px 14px;
    }
    
    .suggestion-chips {
        padding: 0 16px;
    }
    
    .message-bubble {
        font-size: 0.9rem;
        padding: 8px 14px;
    }
    
    .suggestion-chip {
        font-size: 0.85rem;
        padding: 6px 12px;
    }
}
/* Ensure the chat container takes full height of its parent */
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    overflow: hidden;
}
.header-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.clear-btn {
    background: rgba(255,255,255,0.1);
    border: none;
    color: #fff;
    font-size: 1.1rem;
    cursor: pointer;
    opacity: 0.8;
    transition: all 0.2s;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.clear-btn:hover {
    opacity: 1;
    background: rgba(255,255,255,0.2);
    transform: scale(1.1);
}

/* Modern loading animation */
.loading-dots {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 8px 12px;
    background: rgba(0,0,0,0.05);
    border-radius: 12px;
    margin: 4px 0;
}

.loading-dots span {
    width: 8px;
    height: 8px;
    background: var(--primary);
    border-radius: 50%;
    animation: bounce 1.4s infinite ease-in-out;
}

.loading-dots span:nth-child(1) { animation-delay: -0.32s; }
.loading-dots span:nth-child(2) { animation-delay: -0.16s; }

@keyframes bounce {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
}

/* Reply feature */
.reply-preview {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px; /* Reduced padding */
    background: var(--primary-light);
    border-radius: 8px;
    margin-bottom: 6px; /* Reduced margin */
    font-size: 0.85rem; /* Smaller font size */
}

.reply-preview .reply-content {
    flex: 1;
    color: var(--primary-dark);
}

.reply-preview .cancel-reply {
    color: #666;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
}

.reply-preview .cancel-reply:hover {
    background: rgba(0,0,0,0.05);
}

/* Statistics Table */
.stats-table {
    width: 100%;
    border-collapse: collapse;
    margin: 10px 0;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    table-layout: fixed;
}

.stats-table th,
.stats-table td {
    padding: 12px; /* Reduced padding */
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
    word-wrap: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
    font-size: 0.95rem; /* Smaller font size */
}

.stats-table th {
    background: var(--primary-light);
    color: var(--primary-dark);
    font-weight: 600;
    position: sticky;
    top: 0;
    z-index: 1;
}

/* Column widths */
.stats-table th:nth-child(1), /* Phòng ban */
.stats-table td:nth-child(1) {
    width: 25%;
}

.stats-table th:nth-child(2), /* Số NV */
.stats-table td:nth-child(2) {
    width: 10%;
}

.stats-table th:nth-child(3), /* Lương TB */
.stats-table td:nth-child(3) {
    width: 15%;
}

.stats-table th:nth-child(4), /* Lương thấp nhất */
.stats-table td:nth-child(4) {
    width: 15%;
}

.stats-table th:nth-child(5), /* Lương cao nhất */
.stats-table td:nth-child(5) {
    width: 15%;
}

.stats-table th:nth-child(6), /* Tổng lương */
.stats-table td:nth-child(6) {
    width: 20%;
}

.stats-table tr:last-child td {
    border-bottom: none;
}

.stats-table tr:hover td {
    background: var(--primary-light);
}

.stats-table .highlight {
    color: var(--primary-dark);
    font-weight: 600;
}

.stats-table .number {
    text-align: right;
    font-family: 'Roboto Mono', monospace;
    white-space: nowrap;
}

.stats-table .status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.9em;
    font-weight: 500;
    white-space: nowrap;
}

/* Responsive table */
@media screen and (max-width: 768px) {
    .stats-table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    
    .stats-table th,
    .stats-table td {
        padding: 8px;
        font-size: 0.9em;
    }
    
    .stats-table .number {
        font-size: 0.85em;
    }
}

/* Text truncation for long content */
.stats-table td {
    max-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: normal;
}

/* Hover effect to show full content */
.stats-table td:hover {
    white-space: normal;
    overflow: visible;
    position: relative;
    z-index: 2;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Status colors */
.stats-table .status.approved {
    background: #dcfce7;
    color: #166534;
}

.stats-table .status.pending {
    background: #fef9c3;
    color: #854d0e;
}

.stats-table .status.rejected {
    background: #fee2e2;
    color: #991b1b;
}

.stats-table .status.cancelled {
    background: #f3f4f6;
    color: #4b5563;
}

/* Empty state styling */
.stats-table tr.no-data td {
    text-align: center;
    color: #6b7280;
    font-style: italic;
    padding: 20px;
}

/* Loading state */
.stats-table.loading {
    opacity: 0.7;
    pointer-events: none;
}

.stats-table.loading::after {
    content: 'Đang tải...';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: var(--primary);
    font-weight: 500;
}

/* New Components Styling */
.employee-details,
.salary-history,
.bonuses-list,
.org-structure,
.training-info {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin: 8px 0;
}

.employee-card,
.salary-record,
.bonus-item,
.department-card,
.training-record {
    background: #fff;
    border-radius: 12px;
    padding: 16px; /* Reduced padding */
    box-shadow: var(--shadow-sm);
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
    margin-bottom: 16px; /* Reduced margin */
}

.employee-card:hover,
.salary-record:hover,
.bonus-item:hover,
.department-card:hover,
.training-record:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.employee-card h3,
.salary-record h3,
.bonus-item h3,
.department-card h3,
.training-record h3 {
    color: var(--primary-dark);
    margin: 0 0 12px 0; /* Reduced margin */
    font-size: 1.1rem; /* Smaller font size */
    font-weight: 600;
}

.employee-card p,
.salary-record p,
.bonus-item p,
.department-card p,
.training-record p {
    margin: 8px 0; /* Reduced margin */
    color: #4b5563;
    font-size: 0.95rem; /* Smaller font size */
    line-height: 1.5;
}

.employee-card strong,
.salary-record strong,
.bonus-item strong,
.department-card strong,
.training-record strong {
    color: #1f2937;
    font-weight: 600;
}

/* Status badges */
.status-badge {
    display: inline-block;
    padding: 3px 6px; /* Reduced padding */
    border-radius: 12px;
    font-size: 0.8rem; /* Smaller font size */
    font-weight: 500;
    margin-left: 8px;
}

.status-badge.approved {
    background: #dcfce7;
    color: #166534;
}

.status-badge.pending {
    background: #fef9c3;
    color: #854d0e;
}

.status-badge.rejected {
    background: #fee2e2;
    color: #991b1b;
}

.status-badge.cancelled {
    background: #f3f4f6;
    color: #4b5563;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .employee-card,
    .salary-record,
    .bonus-item,
    .department-card,
    .training-record {
        padding: 12px;
    }

    .employee-card h3,
    .salary-record h3,
    .bonus-item h3,
    .department-card h3,
    .training-record h3 {
        font-size: 1rem;
    }

    .employee-card p,
    .salary-record p,
    .bonus-item p,
    .department-card p,
    .training-record p {
        font-size: 0.9rem;
    }
}

/* Updated stats table */
.stats-table th,
.stats-table td {
    padding: 12px; /* Reduced padding */
    font-size: 0.95rem; /* Smaller font size */
}

/* Responsive adjustments */
@media (max-width: 1200px) {
    .chat-widget-box {
        max-width: 100%;
        height: 100vh;
    }
}

@media (max-width: 768px) {
    .chat-widget-box {
        border-radius: 0;
    }
    
    .chat-widget-header {
        padding: 10px 14px;
    }
    
    .chat-widget-messages {
        padding: 12px 10px 6px 10px;
    }
    
    .chat-widget-input {
        padding: 10px 14px;
    }
    
    .suggestion-chips {
        padding: 0 16px;
    }
    
    .message-bubble {
        font-size: 0.9rem;
        padding: 8px 14px;
    }
    
    .suggestion-chip {
        font-size: 0.85rem;
        padding: 6px 12px;
    }
}
</style>
<div class="chat-widget-box">
    <div class="chat-widget-header">
        <div class="title">
            <span class="status-dot"></span>
        </div>
        <div class="header-actions">
            <button class="clear-btn" title="Xóa tất cả tin nhắn">
                <i class="fas fa-broom"></i>
            </button>
            <button class="close-btn" onclick="window.parent.postMessage('closeChatModal','*')">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div class="chat-widget-messages" id="chatMessages">
        <div class="message-row bot">
            <div class="message-avatar"><img src="smile.png" alt="Bot" style="width:32px;height:32px;border-radius:50%;object-fit:cover;"></div>
            <div class="message-bubble">
                Xin chào! Tôi có thể giúp gì cho bạn về hệ thống quản lý nhân sự?
                <div class="message-status">
                    <i class="fas fa-check"></i> Đã gửi
                </div>
            </div>
        </div>
        <div class="suggestion-chips">
            <button class="suggestion-chip"><span>Tổng số nhân viên</span></button>
            <button class="suggestion-chip"><span>Thông tin phòng ban</span></button>
            <button class="suggestion-chip"><span>Thống kê lương</span></button>
            <button class="suggestion-chip"><span>Thống kê nghỉ phép</span></button>
            <button class="suggestion-chip"><span>Nhân viên mới</span></button>
            <button class="suggestion-chip"><span>Thông tin chi tiết nhân viên</span></button>
            <button class="suggestion-chip"><span>Lịch sử lương</span></button>
            <button class="suggestion-chip"><span>Thưởng và phúc lợi</span></button>
            <button class="suggestion-chip"><span>Cơ cấu tổ chức</span></button>
            <button class="suggestion-chip"><span>Thông tin đào tạo</span></button>
        </div>
    </div>
    <div class="typing-indicator" id="typingIndicator" style="display:none;">
        <span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span>
        <span style="font-size:0.95em;color:#888;">Đang trả lời...</span>
    </div>
    <form class="chat-widget-input" id="chatForm" autocomplete="off">
        <div class="input-wrapper">
            <input type="text" id="userInput" placeholder="Nhập câu hỏi..." autocomplete="off">
            <div class="input-actions">
                <button type="button" class="action-btn" id="emojiBtn"><i class="far fa-smile"></i></button>
                <button type="button" class="action-btn" id="fileBtn"><i class="fas fa-paperclip"></i></button>
            </div>
            <div class="emoji-picker" id="emojiPicker">
                <!-- Emojis will be added dynamically -->
            </div>
            <input type="file" class="file-upload" id="fileUpload" multiple>
        </div>
        <button type="submit" class="send-btn"><i class="fas fa-paper-plane"></i></button>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Emoji list
const emojis = ['😊', '😂', '❤️', '👍', '🎉', '🔥', '✨', '💯', '🤔', '👏', '🙏', '💪', '🎯', '📊', '📈', '📝', '📌', '🔍', '💼', '👨‍💼', '👩‍💼', '📅', '⏰', '💰', '📱'];

// Initialize emoji picker
function initEmojiPicker() {
    const picker = $('#emojiPicker');
    emojis.forEach(emoji => {
        picker.append(`<div class="emoji-item">${emoji}</div>`);
    });
}

// Handle emoji selection
$(document).on('click', '.emoji-item', function() {
    const emoji = $(this).text();
    const input = $('#userInput');
    input.val(input.val() + emoji);
    $('#emojiPicker').removeClass('active');
});

// Toggle emoji picker
$('#emojiBtn').click(function(e) {
    e.stopPropagation();
    $('#emojiPicker').toggleClass('active');
});

// Close emoji picker when clicking outside
$(document).click(function(e) {
    if (!$(e.target).closest('.emoji-picker, #emojiBtn').length) {
        $('#emojiPicker').removeClass('active');
    }
});

// Handle file upload
$('#fileBtn').click(function() {
    $('#fileUpload').click();
});

$('#fileUpload').change(function() {
    const files = this.files;
    if (files.length > 0) {
        // Handle file preview and upload
        handleFiles(files);
    }
});

function handleFiles(files) {
    Array.from(files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const filePreview = `
                <div class="file-preview">
                    <div class="file-info">
                        <i class="fas fa-file"></i> ${file.name}
                    </div>
                    <button type="button" class="remove-file">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            $('#chatMessages').append(filePreview);
        };
        reader.readAsDataURL(file);
    });
}

// Remove file preview
$(document).on('click', '.remove-file', function() {
    $(this).closest('.file-preview').remove();
});

// Add new message handling functions
function handleEmployeeDetails(data) {
    let message = '<div class="employee-details">';
    data.forEach(employee => {
        message += `
            <div class="employee-card">
                <h3>${employee.name}</h3>
                <p><strong>Email:</strong> ${employee.email}</p>
                <p><strong>Phone:</strong> ${employee.phone}</p>
                <p><strong>Mã nhân viên:</strong> ${employee.employee_code}</p>
                <p><strong>Phòng ban:</strong> ${employee.department_name}</p>
                <p><strong>Chức vụ:</strong> ${employee.position_name}</p>
                <p><strong>Ngày sinh:</strong> ${employee.date_of_birth}</p>
                <p><strong>Giới tính:</strong> ${employee.gender}</p>
                <p><strong>Địa chỉ thường trú:</strong> ${employee.permanent_address}</p>
                <p><strong>Địa chỉ hiện tại:</strong> ${employee.current_address}</p>
                <p><strong>Người liên hệ khẩn cấp:</strong> ${employee.emergency_contact_name}</p>
                <p><strong>Số điện thoại liên hệ khẩn cấp:</strong> ${employee.emergency_contact_phone}</p>
                <p><strong>Loại hợp đồng:</strong> ${employee.contract_type}</p>
                <p><strong>Ngày bắt đầu hợp đồng:</strong> ${employee.contract_start_date}</p>
                <p><strong>Ngày kết thúc hợp đồng:</strong> ${employee.contract_end_date}</p>
                <p><strong>Lương cơ bản:</strong> ${employee.base_salary}</p>
            </div>
        `;
    });
    message += '</div>';
    return message;
}

function handleSalaryHistory(data) {
    let message = '<div class="salary-history">';
    data.forEach(record => {
        message += `
            <div class="salary-record">
                <h3>${record.employee_name}</h3>
                <p><strong>Ngày hiệu lực:</strong> ${record.effective_date}</p>
                <p><strong>Lương cũ:</strong> ${record.previous_salary}</p>
                <p><strong>Lương mới:</strong> ${record.new_salary}</p>
                <p><strong>Lý do:</strong> ${record.reason}</p>
                <p><strong>Ngày tạo:</strong> ${record.created_at}</p>
            </div>
        `;
    });
    message += '</div>';
    return message;
}

function handleBonuses(data) {
    let message = '<div class="bonuses-list">';
    data.forEach(bonus => {
        message += `
            <div class="bonus-item">
                <h3>${bonus.employee_name}</h3>
                <p><strong>Loại thưởng:</strong> ${bonus.bonus_type}</p>
                <p><strong>Số tiền:</strong> ${bonus.amount}</p>
                <p><strong>Ngày hiệu lực:</strong> ${bonus.effective_date}</p>
                <p><strong>Lý do:</strong> ${bonus.reason}</p>
                <p><strong>Trạng thái:</strong> ${bonus.status}</p>
            </div>
        `;
    });
    message += '</div>';
    return message;
}

function handleOrganizationStructure(data) {
    let message = '<div class="org-structure">';
    data.forEach(dept => {
        message += `
            <div class="department-card">
                <h3>${dept.department_name}</h3>
                <p><strong>Mô tả:</strong> ${dept.description}</p>
                <p><strong>Số nhân viên:</strong> ${dept.employee_count}</p>
                <p><strong>Trưởng phòng:</strong> ${dept.manager_name}</p>
                <p><strong>Phòng ban cha:</strong> ${dept.parent_department}</p>
            </div>
        `;
    });
    message += '</div>';
    return message;
}

function handleTrainingInfo(data) {
    let message = '<div class="training-info">';
    data.forEach(record => {
        message += `
            <div class="training-record">
                <h3>${record.employee_name}</h3>
                <p><strong>Khóa học:</strong> ${record.course_name}</p>
                <p><strong>Ngày đăng ký:</strong> ${record.registration_date}</p>
                <p><strong>Trạng thái:</strong> ${record.course_status}</p>
                <p><strong>Ngày hoàn thành:</strong> ${record.completion_date}</p>
                <p><strong>Điểm số:</strong> ${record.score}</p>
                <p><strong>Chứng chỉ:</strong> ${record.certificate_name}</p>
                <p><strong>Tổ chức cấp:</strong> ${record.issuing_organization}</p>
                <p><strong>Ngày cấp:</strong> ${record.issue_date}</p>
                <p><strong>Ngày hết hạn:</strong> ${record.expiry_date}</p>
            </div>
        `;
    });
    message += '</div>';
    return message;
}

// Enhanced message formatting with markdown support
function formatMessage(message) {
    // Handle JSON responses for new queries
    if (typeof message === 'object' || (typeof message === 'string' && message.startsWith('{'))) {
        try {
            const data = typeof message === 'string' ? JSON.parse(message) : message;
            if (data.type === 'employee_details') {
                return handleEmployeeDetails(data.data);
            } else if (data.type === 'salary_history') {
                return handleSalaryHistory(data.data);
            } else if (data.type === 'bonuses') {
                return handleBonuses(data.data);
            } else if (data.type === 'org_structure') {
                return handleOrganizationStructure(data.data);
            } else if (data.type === 'training_info') {
                return handleTrainingInfo(data.data);
            }
        } catch (e) {
            console.error('Error parsing JSON response:', e);
            return message; // Return original message if parsing fails
        }
    }

    // Ensure message is a string before using string methods
    if (typeof message !== 'string') {
        message = String(message);
    }

    // Handle statistics tables
    message = message.replace(/\[TABLE\]([\s\S]*?)\[\/TABLE\]/g, function(match, tableData) {
        try {
            const data = JSON.parse(tableData);
            let html = '<table class="stats-table">';
            
            // Add headers
            if (data.headers) {
                html += '<thead><tr>';
                data.headers.forEach(header => {
                    html += `<th>${header}</th>`;
                });
                html += '</tr></thead>';
            }
            
            // Add rows
            if (data.rows) {
                html += '<tbody>';
                data.rows.forEach(row => {
                    html += '<tr>';
                    row.forEach((cell, index) => {
                        const cellClass = data.cellClasses && data.cellClasses[index] ? data.cellClasses[index] : '';
                        html += `<td class="${cellClass}">${cell}</td>`;
                    });
                    html += '</tr>';
                });
                html += '</tbody>';
            }
            
            html += '</table>';
            return html;
        } catch (e) {
            console.error('Error parsing table data:', e);
            return match;
        }
    });
    
    // Handle code blocks with syntax highlighting
    message = message.replace(/```(\w+)?\n([\s\S]*?)```/g, (match, lang, code) => {
        return formatCodeBlock(code.trim(), lang);
    });
    
    // Handle inline code
    message = message.replace(/`([^`]+)`/g, '<code>$1</code>');
    
    // Handle blockquotes
    message = message.replace(/^> (.+)$/gm, '<blockquote>$1</blockquote>');
    
    // Handle links
    message = message.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank">$1</a>');
    
    // Handle line breaks
    message = message.replace(/\n/g, '<br>');
    
    return message;
}

// Enhanced code block formatting
function formatCodeBlock(code, language) {
    const lang = language || 'plaintext';
    return `
        <div class="code-block">
            <pre><code class="language-${lang}">${escapeHtml(code)}</code></pre>
            <button class="copy-btn" title="Copy code">
                <i class="fas fa-copy"></i>
            </button>
        </div>
    `;
}

// HTML escape function
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Reply functionality
let replyingTo = null;

function setReplyTo(messageId, content) {
    replyingTo = { id: messageId, content };
    showReplyPreview();
}

function showReplyPreview() {
    if (!replyingTo) return;
    
    const preview = `
        <div class="reply-preview">
            <div class="reply-content">
                <strong>Replying to:</strong> ${replyingTo.content}
            </div>
            <button class="cancel-reply" onclick="cancelReply()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    $('.chat-widget-input').prepend(preview);
}

function cancelReply() {
    replyingTo = null;
    $('.reply-preview').remove();
}

// Enhanced message handling
function appendMessage(message, sender) {
    const messageId = 'msg_' + Date.now();
    const avatarImg = sender === 'user' 
        ? '<img src="mom.png" alt="User" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">'
        : '<img src="smile.png" alt="Bot" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">';
    
    const messageRow = $(`
        <div class="message-row ${sender}" id="${messageId}">
            <div class="message-avatar">
                ${avatarImg}
            </div>
            <div class="message-content">
                <div class="message-bubble">
                    ${formatMessage(message)}
                    <div class="message-status">
                        <i class="fas fa-check"></i> Đã gửi
                    </div>
                </div>
            </div>
        </div>
    `).hide();
    
    $('#chatMessages').append(messageRow);
    messageRow.fadeIn(300);
    scrollToBottom();
}

// Copy message function
function copyMessage(messageId) {
    const message = $(`#${messageId} .message-bubble`).text();
    navigator.clipboard.writeText(message).then(() => {
        // Show copied feedback
        const btn = $(`#${messageId} .message-action-btn[title="Copy"]`);
        btn.html('<i class="fas fa-check"></i>');
        setTimeout(() => {
            btn.html('<i class="fas fa-copy"></i>');
        }, 2000);
    });
}

// Add smooth scroll with offset
function scrollToBottom() {
    const chat = document.getElementById('chatMessages');
    const scrollHeight = chat.scrollHeight;
    const currentScroll = chat.scrollTop;
    const targetScroll = scrollHeight - chat.clientHeight;
    
    if (targetScroll > currentScroll) {
        chat.scrollTo({
            top: targetScroll,
            behavior: 'smooth'
        });
    }
}

// Add typing animation
function simulateTyping(message, callback) {
    let i = 0;
    const speed = 30;
    const typingIndicator = $('#typingIndicator');
    
    typingIndicator.show();
    
    function typeWriter() {
        if (i < message.length) {
            callback(message.substring(0, i + 1));
            i++;
            setTimeout(typeWriter, speed);
        } else {
            typingIndicator.hide();
        }
    }
    
    typeWriter();
}

// Initialize
$(document).ready(function() {
    initEmojiPicker();
    
    // Clear chat functionality
    $('.clear-btn').on('click', function() {
        if(confirm('Bạn có chắc muốn xóa tất cả tin nhắn?')) {
            // Clear all messages
            $('#chatMessages').empty();
            
            // Add welcome message back
            const welcomeMessage = `
                <div class="message-row bot">
                    <div class="message-avatar"><img src="smile.png" alt="Bot" style="width:32px;height:32px;border-radius:50%;object-fit:cover;"></div>
                    <div class="message-bubble">
                        Xin chào! Tôi có thể giúp gì cho bạn về hệ thống quản lý nhân sự?
                        <div class="message-status">
                            <i class="fas fa-check"></i> Đã gửi
                        </div>
                    </div>
                </div>
            `;
            
            // Add suggestion chips back
            const suggestionChips = `
                <div class="suggestion-chips">
                    <button class="suggestion-chip"><span>Tổng số nhân viên</span></button>
                    <button class="suggestion-chip"><span>Thông tin phòng ban</span></button>
                    <button class="suggestion-chip"><span>Thống kê lương</span></button>
                    <button class="suggestion-chip"><span>Thống kê nghỉ phép</span></button>
                    <button class="suggestion-chip"><span>Nhân viên mới</span></button>
                    <button class="suggestion-chip"><span>Thông tin chi tiết nhân viên</span></button>
                    <button class="suggestion-chip"><span>Lịch sử lương</span></button>
                    <button class="suggestion-chip"><span>Thưởng và phúc lợi</span></button>
                    <button class="suggestion-chip"><span>Cơ cấu tổ chức</span></button>
                    <button class="suggestion-chip"><span>Thông tin đào tạo</span></button>
                </div>
            `;
            
            // Append welcome message and suggestion chips
            $('#chatMessages').append(welcomeMessage);
            $('#chatMessages').append(suggestionChips);
            
            // Focus input for new conversation
            $('#userInput').focus();
        }
    });
    
    // Gửi tin nhắn
    $('#chatForm').on('submit', function(e) {
        e.preventDefault();
        const userInput = $('#userInput').val().trim();
        if(userInput) {
            let message = userInput;
            
            appendMessage(message, 'user');
            $('#userInput').val('');
            $('#typingIndicator').show();
            scrollToBottom();
            
            // Send request
            $.ajax({
                url: 'process_chat.php',
                method: 'POST',
                data: { 
                    message: userInput
                },
                dataType: 'json',
                success: function(response) {
                    setTimeout(function() {
                        $('#typingIndicator').hide();
                        try {
                            if (typeof response === 'string') {
                                response = JSON.parse(response);
                            }
                            
                            if (response && response.success) {
                                appendMessage(response.response || 'Không có phản hồi', 'bot');
                            } else {
                                const errorMsg = response && response.error ? response.error : 'Vui lòng thử lại sau.';
                                console.error('Chat error:', errorMsg);
                                appendMessage('Xin lỗi, đã có lỗi xảy ra: ' + errorMsg, 'bot');
                            }
                        } catch (e) {
                            console.error('Parse error:', e);
                            appendMessage('Xin lỗi, đã có lỗi xảy ra khi xử lý phản hồi. Vui lòng thử lại sau.', 'bot');
                        }
                    }, 600);
                },
                error: function(xhr, status, error) {
                    $('#typingIndicator').hide();
                    let errorMessage = 'Xin lỗi, đã có lỗi xảy ra khi kết nối với server.';
                    
                    try {
                        const response = xhr.responseText ? JSON.parse(xhr.responseText) : null;
                        if (response && response.error) {
                            errorMessage += ' Chi tiết: ' + response.error;
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                    
                    console.error('Chat error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    
                    appendMessage(errorMessage, 'bot');
                }
            });
        }
    });
    
    // Gợi ý nhanh - Sử dụng event delegation
    $(document).on('click', '.suggestion-chip', function() {
        const text = $(this).text();
        $('#userInput').val(text);
        $('#chatForm').submit();
    });
    
    // Đóng chat nếu click nút X (gửi message cho parent window)
    window.addEventListener('message', function(event) {
        if(event.data === 'closeChatModal') {
            if(window.parent && window.parent.$) {
                window.parent.$('#chatModal').modal('hide');
            }
        }
    });
    
    // Focus input khi load
    $('#userInput').focus();
});
</script> 