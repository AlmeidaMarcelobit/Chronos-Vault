<header>
    <nav class="nav" aria-label="Main navigation">
        <ul>
            <li><a href="../includes/home.php">Home</a></li>
            <li class="drop" aria-haspopup="true" aria-expanded="false">
                <a href="#">Colaboradores ▾</a>
                <ul class="dropdown" aria-label="Submenu">
                    <li><a href="../categorias/new-collaborators.php">Novos Colaboradres</a></li>
                    <li><a href="../collaborators/collaborators-global.php">Colaboradores</a></li>
                </ul>
            </li>
            <li class="drop" aria-haspopup="true" aria-expanded="false">
                <a href="#">Anotação ▾</a>
                <ul class="dropdown" aria-label="Submenu">
                    <li><a href="../../apps/annotation/add.php">Adicionar</a></li>
                    <li><a href="../../apps/annotation/view.php">Anotação</a></li>
                </ul>
            <li class="drop" aria-haspopup="true" aria-expanded="false">
                <a href="#">Estoque ▾</a>
                <ul class="dropdown" aria-label="Submenu">
                    <li><a href="../categorias/cell-phone.php">Celular</a></li>
                    <li><a href="../categorias/fone.php">Fones</a></li>
                    <li><a href="../categorias/kits.php">Kits</a></li>
                    <li><a href="../categorias/monitor.php">Monitor</a></li>
                    <li><a href="../categorias/notebook.php">Notebooks</a></li>
                    <li><a href="../categorias/support.php">Suporte</a></li>
                </ul>

            </li>
        </ul>
        <div>
            <form onsubmit="event.preventDefault(); buscarColaboradores();" class="form-busca">
                <input type="text" id="busca" name="busca" placeholder="Nome ou patrimônio" class="input-busca">

                <button type="submit" class="btn btn-buscar">🔍 Buscar</button>

                <button type="button" class="btn btn-limpar" onclick="limparBusca();">🧹 Limpar</button>
            </form>
            <!--
            <form onsubmit="event.preventDefault(); buscarColaboradores();">
                <input type="text" id="busca" name="busca" placeholder="Nome ou patrimônio">
                <button type="submit">Buscar</button>
                <button type="button" class="limpar" onclick="limparBusca();">Limpar</button>
            </form>
            -->
        </div>
    </nav>

</header>
