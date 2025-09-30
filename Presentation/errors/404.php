<div class="container">
    <div class="card">
        <div style="text-align: center; padding: 2rem;">
            <i class="fas fa-exclamation-triangle" style="font-size: 4rem; color: var(--danger-color); margin-bottom: 1rem;"></i>
            <h1 style="color: var(--danger-color); margin-bottom: 1rem;">Error <?= $status ?? '404' ?></h1>
            <h2 style="margin-bottom: 1rem;"><?= $message ?? 'Página no encontrada' ?></h2>
            <p style="color: #6c757d; margin-bottom: 2rem;">
                Lo sentimos, pero la página que buscas no existe o ha ocurrido un error.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="/supabase" role="button" class="primary">
                    <i class="fas fa-home"></i> Ir al Inicio
                </a>
                <a href="/supabase/users" role="button" class="secondary">
                    <i class="fas fa-users"></i> Ver Usuarios
                </a>
                <a href="/supabase/users/create" role="button" class="secondary">
                    <i class="fas fa-plus"></i> Crear Usuario
                </a>
            </div>
        </div>
    </div>
</div>
