<style>
    * {
        border-radius: 0 !important;
    }

    .brutal-sidebar {
        background: white;
        border: 4px solid black;
        position: sticky;
        top: 100px;
    }

    .brutal-sidebar-header {
        padding: 24px;
        border-bottom: 4px solid black;
        background: white;
    }

    .brutal-avatar {
        width: 64px;
        height: 64px;
        background: black;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 900;
        flex-shrink: 0;
    }

    .brutal-sidebar-item {
        padding: 20px 24px;
        border-bottom: 2px solid #e0e0e0;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        color: black;
    }

    .brutal-sidebar-item:hover {
        background: #f5f5f5;
        text-decoration: none;
        color: black;
    }

    .brutal-sidebar-item.active {
        background: black;
        color: white;
        border-color: black;
    }

    .brutal-sidebar-item.active:hover {
        background: black;
        color: white;
    }

    .brutal-badge {
        background: black;
        color: white;
        padding: 2px 10px;
        font-size: 12px;
        font-weight: 900;
        border: 2px solid black;
    }

    .brutal-sidebar-item.active .brutal-badge {
        background: white;
        color: black;
    }

    .brutal-content-card {
        background: white;
        border: 4px solid black;
        padding: 32px;
        margin-bottom: 24px;
    }

    .brutal-title {
        font-size: 36px;
        font-weight: 900;
        margin-bottom: 32px;
        text-transform: uppercase;
    }

    .brutal-subtitle {
        font-size: 24px;
        font-weight: 800;
        margin-bottom: 24px;
        text-transform: uppercase;
    }

    .brutal-stat-box {
        text-align: center;
        padding: 20px;
        border: 2px solid black;
        background: white;
        transition: all 0.2s ease;
    }

    .brutal-stat-box:hover {
        box-shadow: 4px 4px 0 black;
    }

    .brutal-stat-number {
        font-size: 32px;
        font-weight: 900;
        margin-bottom: 8px;
    }

    .brutal-stat-label {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 14px;
    }

    .brutal-input {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid black;
        font-size: 16px;
        font-weight: 500;
        background: white;
        transition: all 0.2s ease;
    }

    .brutal-input:focus {
        outline: none;
        background: #f9f9f9;
        box-shadow: 4px 4px 0 black;
    }

    .brutal-label {
        font-weight: 700;
        margin-bottom: 8px;
        text-transform: uppercase;
        display: block;
        font-size: 14px;
    }

    .brutal-btn-black {
        background: black;
        color: white;
        border: 2px solid black;
        padding: 16px 32px;
        font-weight: 700;
        font-size: 16px;
        text-transform: uppercase;
        transition: all 0.2s ease;
        cursor: pointer;
        display: inline-block;
        text-decoration: none;
    }

    .brutal-btn-black:hover {
        background: white;
        color: black;
        text-decoration: none;
    }

    .brutal-btn-outline {
        background: white;
        color: black;
        border: 2px solid black;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.2s ease;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .brutal-btn-outline:hover {
        background: black;
        color: white;
        text-decoration: none;
    }

    .brutal-btn-danger {
        background: white;
        color: #dc3545;
        border: 2px solid #dc3545;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .brutal-btn-danger:hover {
        background: #dc3545;
        color: white;
    }

    .brutal-order-card {
        border: 4px solid black;
        padding: 24px;
        background: white;
        margin-bottom: 24px;
    }

    .brutal-order-status {
        padding: 4px 12px;
        font-weight: 700;
        font-size: 12px;
        color: white;
        text-transform: uppercase;
        display: inline-block;
    }

    .status-processing { background: #ff9500; }
    .status-shipped { background: #007aff; }
    .status-delivered { background: #34c759; }
    .status-cancelled { background: #ff3b30; }
    .status-pending { background: #6c757d; }
    .status-new { background: #6c757d; }

    .brutal-product-thumb {
        width: 80px;
        height: 80px;
        border: 2px solid black;
        background: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .brutal-progress-bar {
        height: 12px;
        background: #e0e0e0;
        border: 2px solid black;
        overflow: hidden;
    }

    .brutal-progress-fill {
        height: 100%;
        background: black;
        transition: width 0.5s ease;
    }

    .brutal-table {
        width: 100%;
        border-collapse: collapse;
    }

    .brutal-table th {
        background: black;
        color: white;
        padding: 12px 16px;
        text-transform: uppercase;
        font-weight: 700;
        font-size: 14px;
        text-align: left;
    }

    .brutal-table td {
        padding: 12px 16px;
        border-bottom: 2px solid #e0e0e0;
        font-weight: 500;
    }

    .brutal-table tr:hover td {
        background: #f5f5f5;
    }

    .brutal-card {
        border: 4px solid black;
        background: white;
        transition: all 0.2s ease;
    }

    .brutal-card:hover {
        box-shadow: 6px 6px 0 black;
    }

    .brutal-empty-state {
        text-align: center;
        padding: 60px 20px;
        border: 4px dashed black;
        background: #fafafa;
    }

    .brutal-empty-state-icon {
        font-size: 64px;
        margin-bottom: 16px;
    }

    .brutal-empty-state-text {
        font-size: 20px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .brutal-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        z-index: 1040;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .brutal-modal {
        background: white;
        border: 4px solid black;
        width: 100%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
        margin: 20px;
    }

    .brutal-modal-header {
        padding: 24px;
        border-bottom: 4px solid black;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .brutal-modal-body {
        padding: 24px;
    }

    .brutal-checkbox-label {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        font-weight: 600;
        cursor: pointer;
    }

    .brutal-checkbox {
        width: 24px;
        height: 24px;
        border: 2px solid black;
        appearance: none;
        cursor: pointer;
        flex-shrink: 0;
    }

    .brutal-checkbox:checked {
        background: black;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M13.485 3.515a1 1 0 010 1.414l-6.5 6.5a1 1 0 01-1.414 0l-3.5-3.5a1 1 0 111.414-1.414L6.985 9.5l5.786-5.985a1 1 0 011.414 0z'/%3E%3C/svg%3E");
    }

    .brutal-tier-card {
        border: 4px solid black;
        padding: 24px;
        text-align: center;
        transition: all 0.2s ease;
    }

    .brutal-tier-card.active {
        box-shadow: 6px 6px 0 black;
    }

    @media (max-width: 991px) {
        .brutal-sidebar {
            position: static;
            margin-bottom: 24px;
        }

        .brutal-title {
            font-size: 28px;
        }

        .brutal-subtitle {
            font-size: 20px;
        }
    }
</style>
