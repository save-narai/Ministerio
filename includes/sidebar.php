<?php

require_once __DIR__ . '/../helpers/ui.php';
require_once __DIR__ . '/../middleware/permiso.php';

?>

<aside class="sidebar">

    <div class="sidebar-content">

        <?php if (tienePermiso('ver_dashboard')): ?>

        <a
            class="<?= menuActivo('/dashboard.php') ?>"
            href="<?= BASE_URL ?>/views/dashboard.php"
        >

            <i class="fa-solid fa-house"></i>

            <span>Dashboard</span>

        </a>

        <?php endif; ?>


        <?php if (tienePermiso('gestionar_jovenes')): ?>

        <a
            class="<?= menuActivo('/jovenes/') ?>"
            href="<?= BASE_URL ?>/views/jovenes/index.php"
        >

            <i class="fa-solid fa-users"></i>

            <span>Jóvenes</span>

        </a>

        <?php endif; ?>


        <?php if (tienePermiso('gestionar_reuniones')): ?>

        <a
            class="<?= menuActivo('/reuniones/') ?>"
            href="<?= BASE_URL ?>/views/reuniones/index.php"
        >

            <i class="fa-solid fa-calendar"></i>

            <span>Reuniones</span>

        </a>

        <?php endif; ?>


        <?php if (tienePermiso('gestionar_seguimientos')): ?>

        <a
            class="<?= menuActivo('/seguimientos/') ?>"
            href="<?= BASE_URL ?>/views/seguimientos/index.php"
        >

            <i class="fa-solid fa-notes-medical"></i>

            <span>Seguimientos</span>

        </a>

        <?php endif; ?>


        <?php if (tienePermiso('gestionar_usuarios')): ?>

        <a
            class="<?= menuActivo('/usuarios/') ?>"
            href="<?= BASE_URL ?>/views/usuarios/index.php"
        >

            <i class="fa-solid fa-users-gear"></i>

            <span>Usuarios</span>

        </a>

        <?php endif; ?>


        <?php if (
            tienePermiso('gestionar_roles')
            || esAdmin()
        ): ?>

        <a
            class="<?= menuActivo('/roles/') ?>"
            href="<?= BASE_URL ?>/views/roles/index.php"
        >

            <i class="fa-solid fa-gear"></i>

            <span>Roles</span>

        </a>

        <?php endif; ?>


        <a href="<?= BASE_URL ?>/logout.php">

            <i class="fa-solid fa-right-from-bracket"></i>

            <span>Salir</span>

        </a>

    </div>

</aside>