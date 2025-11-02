<script>
    {{-- Utility Functions --}}

    // ========================================
    // RELOAD PAGE (Refresh all data)
    // ========================================
    function reloadPage() {
        // Refresh the entire page to clear all data and reload fresh
        window.location.reload();
    }

    // ========================================
    // RESET FORM (Legacy - now redirects to reload)
    // ========================================
    function resetForm() {
        reloadPage();
    }
</script>
