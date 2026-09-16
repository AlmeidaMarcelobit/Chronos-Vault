<?php
session_start();

// Impede acesso direto sem login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../../apps/sessao/login.html");
    exit();
}
?>
<html lang="pt-br">

<head>
    <title>Colaboradores</title>
    <?php include '../includes/header-meta.php'; ?>
</head>

<body>
    <?php include '../includes/nav.php'; ?>
    <script>
        function normalizarTexto(texto) {
            return texto.toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .trim();
        }

        function buscarColaboradores() {
            var termoBusca = normalizarTexto(document.getElementById('busca').value);
            if (!termoBusca) {
                alert('Por favor, digite um nome ou patrimônio para buscar');
                return;
            }

            var cards = document.querySelectorAll('.card');
            var encontrou = false;

            cards.forEach(function(card) {
                // Busca pelo nome
                var nome = normalizarTexto(card.querySelector('h3').textContent.replace('👤', ''));

                // Busca por patrimônios
                var patrimonios = Array.from(card.querySelectorAll('.device p'))
                    .filter(p => p.textContent.includes('Patrimônio'))
                    .map(p => normalizarTexto(p.textContent.split(':')[1]));

                // Verifica se encontrou no nome OU em algum patrimônio
                var corresponde = nome.includes(termoBusca) ||
                    patrimonios.some(p => p.includes(termoBusca));

                if (corresponde) {
                    card.style.display = '';
                    encontrou = true;
                } else {
                    card.style.display = 'none';
                }
            });

            if (!encontrou) {
                alert('Nenhum colaborador encontrado com "' + document.getElementById('busca').value + '"');
            }
        }

        function limparBusca() {
            document.getElementById('busca').value = '';
            document.querySelectorAll('.card').forEach(card => {
                card.style.display = '';
            });
        }

    </script>

    <div class="container">
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/alessandra%20rais.jpg" alt="Foto de Alessandra Rais" class="profile-pic">
            <h3>👤 Alessandra Rais</h3>
            <p><strong>Cargo: </strong>Analista de Relacionamento com Profissionais da Saúde</p>
            <p><strong>Departamento: </strong>Relacionamento com Profissionais da Saúde</p>
            <p><strong>E-mail: </strong>alessandra.rais@amorsaude.com</p>
            <p><strong>CPF: </strong>323.024.848-19</p>
            <p><strong>Centro de Custo</strong>12003 - Relacionamento Profissionais da Saúde</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>Dell Vostro 15 3510</p>
                    <p><strong>Série: </strong>DGQN4T3</p>
                    <p><strong>Modelo: </strong>293</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>Logitech H390</p>
                    <p><strong>Série: </strong>2316ME0275M9</p>
                    <p><strong>Modelo: </strong>888</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/alexy%20divino.jpg" alt="Foto de Alexy Chiodi Divino<" class="profile-pic">
            <h3>👤 Alexy Chiodi Divino</h3>
            <p><strong>Cargo: </strong>Gerente de CRM</p>
            <p><strong>Departamento: </strong>CRM</p>
            <p><strong>E-mail: </strong>alexy.divino@amorsaude.com</p>
            <p><strong>CPF: </strong>402.811.958-89</p>
            <p><strong>Centro de Custo</strong>14008 - Gerência de CRM</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>222</p>
                    <p><strong>Série: </strong>92KLOV3</p>
                    <p><strong>Modelo: </strong>Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>657</p>
                    <p><strong>Série: </strong>274048218</p>
                    <p><strong>Modelo: </strong>Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong>663</p>
                    <p><strong>Série: </strong>R9XTA03CT1N</p>
                    <p><strong>Modelo: </strong>Galaxy A03</p>
                    <p><strong>IMI 1: </strong>350916876991657/01</p>
                    <p><strong>IMI 2: </strong>354654236991655/01</p>
                </div>
            </div>
        </div>

        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/aline%20sobral.jpg" alt="Foto de Aline Alves Sobral Da Silva" class="profile-pic">
            <h3>👤 Aline Alves Sobral Da Silva</h3>
            <p><strong>Cargo: </strong>Consultor de Operações</p>
            <p><strong>Departamento: </strong>Louzada</p>
            <p><strong>E-mail: </strong>aline.silva@amorsaude.com</p>
            <p><strong>CPF: </strong>449.168.138-47</p>
            <p><strong>Centro de Custo</strong>18004 - Laboratório Louzada </p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>977</p>
                    <p><strong>Série: </strong>2330AY028C68</p>
                    <p><strong>Modelo: </strong>Logiteh H390</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/aline%20oliveira.jpg" alt="Foto de Aline Camilo de Oliveira" class="profile-pic">
            <h3>👤 Aline Camilo de Oliveira</h3>
            <p><strong>Cargo: </strong>Consultor de Operação</p>
            <p><strong>Departamento: </strong>Consultoria</p>
            <p><strong>E-mail: </strong>aline.oliveira@amorsaude.com</p>
            <p><strong>CPF: </strong>445.597.828-30</p>
            <p><strong>Centro de Custo: </strong>12006 - Regional 1</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>225</p>
                    <p><strong>Série: </strong>pe09xvkr</p>
                    <p><strong>Modelo: </strong>Lenovo v15 G2</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong>339</p>
                    <p><strong>Série: </strong>2234CE1821AB</p>
                    <p><strong>Modelo: </strong>Logitech K270 - M185</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>670</p>
                    <p><strong>Série: </strong>273963767</p>
                    <p><strong>Modelo: </strong>Jabra HSC0016</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong></p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte de Alumínio para Notebook</h4>
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong></p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong>668</p>
                    <p><strong>Série: </strong>r9xta0edmwa</p>
                    <p><strong>Modelo: </strong>Galaxy A03</p>
                    <p><strong>IMI 1: </strong>350916877000847/01</p>
                    <p><strong>IMI 2: </strong>354654237000845/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/allan%20magrini.jpg" alt="Foto de Alessandra Rais" class="profile-pic">
            <h3>👤 Allan Magrini</h3>
            <p><strong>Cargo: </strong>Desenvolvedor Plsql</p>
            <p><strong>Departamento: </strong>Desenvolvimento</p>
            <p><strong>E-mail: </strong>allan.magrini@amorsaude.com</p>
            <p><strong>CPF: </strong>266.169.148-0</p>
            <p><strong>Centro de Custo</strong>13012 - Desenvolvimento</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>441</p>
                    <p><strong>Série: </strong>3vzhny3</p>
                    <p><strong>Modelo: </strong>Dell Latitude 3420</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado</h4>
                    <p><strong>Patrimônio: </strong>443</p>
                    <p><strong>Série: </strong>CN-ONRCC-LO300-331-KOUF-Ao1</p>
                    <p><strong>Modelo: </strong>KB3121WT MS3121WT UD2301</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>904</p>
                    <p><strong>Série: </strong>2316ME027FL9</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte de Alumínio para Notebook</h4>
                    <p><strong>Patrimônio: </strong>903</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Alumínio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/amanda%20reis.jpg" alt="Foto de Amanda da Silva Reis" class="profile-pic">
            <h3>👤 Amanda da Silva Reis</h3>
            <p><strong>Cargo: </strong>Assistente De Canais Digitais E Relacionamento</p>
            <p><strong>Departamento: </strong>Administronline</p>
            <p><strong>E-mail: </strong>amanda.reis@amorsaude.com</p>
            <p><strong>CPF: </strong>432.458.608-03</p>
            <p><strong>Centro de Custo: </strong>22001 - Consulta Assistida</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>03</p>
                    <p><strong>Série: </strong>2tn05l3</p>
                    <p><strong>Modelo: </strong>Dell Inspiron 3501 p90f</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>934</p>
                    <p><strong>Série: </strong>2323GG15JD8</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong>442</p>
                    <p><strong>Série: </strong>307AZMG9G027</p>
                    <p><strong>Modelo: </strong>LG 22MP410</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone (Comprometido)</h4>
                    <p><strong>Patrimônio: </strong>737</p>
                    <p><strong>Série: </strong>2125me0k05n</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/amanda%20marcolino.jpg" alt="Foto de Amanda Dualattka Marcolino" class="profile-pic">
            <h3>👤 Amanda Dualattka Marcolino</h3>
            <p><strong>Cargo: </strong>Assistente de CS</p>
            <p><strong>Departamento: </strong>Atendimento ao Cliente</p>
            <p><strong>E-mail: </strong>amanda.marcolino@amorsaude.com</p>
            <p><strong>CPF: </strong>435.47.258-67</p>
            <p><strong>Centro de Custo: </strong>12002 - SAC</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>313</p>
                    <p><strong>Série: </strong>1VZHNY3</p>
                    <p><strong>Modelo: </strong>Dell Latitude</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio (Teclado): </strong>321</p>
                    <p><strong>Patrimônio (Mouse): </strong>864</p>
                    <p><strong>Série: </strong>CN-ONCRCC-LO300-3111-KOU-A01</p>
                    <p><strong>Modelo: </strong>KM5221W</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong>865</p>
                    <p><strong>Modelo: </strong>Alumínio</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>885</p>
                    <p><strong>Série: </strong>2316ME02F8G8</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/amanda%20gazetta.jpg" alt="Foto de Amanda Gazetta" class="profile-pic">
            <h3>👤 Amanda Gazetta Amigo</h3>
            <p><strong>Cargo: </strong>Coordenadora de Operação</p>
            <p><strong>Departamento: </strong>Regional 2</p>
            <p><strong>E-mail: </strong>amanda.gazetta@amorsaude.com</p>
            <p><strong>CPF: </strong>418.310.088-74</p>
            <p><strong>Centro de Custo: </strong>12007 - Regional 2</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>166</p>
                    <p><strong>Série: </strong>PE09781x</p>
                    <p><strong>Modelo: </strong>Ideapad3 15alc6</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>876</p>
                    <p><strong>Série: </strong>2315Ay07AC39</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Modelo: </strong>Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong>185 - 578</p>
                    <p><strong>Série: </strong>2207LZX4F248</p>
                    <p><strong>Modelo: </strong>Logitech MK235</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Série: </strong>R9QW800QQ6R</p>
                    <p><strong>Modelo: </strong>Galaxy A03 Core</p>
                    <p><strong>IMEI 1: </strong>350589197094384</p>
                    <p><strong>IMEI 2: </strong>353306167094386</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/ana%20beatriz%20melo%20silveir.jpg" alt="Foto de Ana Beatriz Melo Silveira" class="profile-pic">
            <h3>👤 Ana Beatriz Melo Silveira</h3>
            <p><strong>Cargo: </strong>Estagiário(a) Dev</p>
            <p><strong>Departamento: </strong>Desenvolvimento</p>
            <p><strong>E-mail: </strong>anabeatriz.silveira@amorsaude.com</p>
            <p><strong>CPF: </strong>154.666.279-66</p>
            <p><strong>Centro de Custo: </strong>13012 - Desenvolvimento</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>251</p>
                    <p><strong>Série: </strong>5SFQXW3</p>
                    <p><strong>Modelo: </strong>Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>1025</p>
                    <p><strong>Série: </strong>2235ME00Y859</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong>1026</p>
                    <p><strong>Série: </strong>R9XW2CHSDX</p>
                    <p><strong>Modelo: </strong>Galaxy A03 Core</p>
                    <p><strong>IMEI 1: </strong>358302235395678/01</p>
                    <p><strong>IMEI 2: </strong>35996405395674/01</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio:</strong>278</p>
                    <p><strong>Série:</strong>2243CE258449 - 2241lzx3nz18</p>
                    <p><strong>Modelo:</strong>Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio:</strong>824</p>
                    <p><strong>Série:</strong></p>
                    <p><strong>Modelo:</strong>Aluminio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/ana%20carolina%20lima.jpg" alt="Foto de Ana Carolina Delsin de Lima" class="profile-pic">
            <h3>👤 Ana Carolina Delsin de Lima</h3>
            <p><strong>Cargo: </strong>Consultora de Operações</p>
            <p><strong>Departamento: </strong>Consultoria</p>
            <p><strong>E-mail: </strong>anacarolina.lima@amorsaude.com</p>
            <p><strong>CPF: </strong>420.088.578-67</p>
            <p><strong>Centro de Custo: </strong>12006 - Regional 1</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>417</p>
                    <p><strong>Série: </strong>9VZHNY3</p>
                    <p><strong>Modelo: </strong>Dell Latitude 3420</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>1044</p>
                    <p><strong>Série: </strong>2432AYMOH2H8</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>💾 Suporte</h4>
                    <p><strong>Patrimônio: </strong>873</p>
                    <p><strong>Modelo: </strong>Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong>420</p>
                    <p><strong>Série: </strong>CN-0NCRCCLO300-331-KOUD-AO1</p>
                    <p><strong>Modelo: </strong>Dell KM5221W</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong>854</p>
                    <p><strong>Série: </strong>UJ5HVE66JS</p>
                    <p><strong>Modelo: </strong>Motorola E13</p>
                    <p><strong>IMEI 1: </strong>359939802683454</p>
                    <p><strong>IMEI 2: </strong>359939802683462</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto de Ana Carolina Rossi" class="profile-pic">
            <h3>👤 Ana Carolina Rossi</h3>
            <p><strong>Cargo: </strong>Analista Comercial</p>
            <p><strong>Departamento: </strong>Amor Cirurgias</p>
            <p><strong>E-mail: </strong>anacarolina.rossi@amorsaude.com</p>
            <p><strong>CPF: </strong>305.254.438-19</p>
            <p><strong>Centro de Custo: </strong>32001 - Administronline</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>013</p>
                    <p><strong>Série: </strong>G7LYXP3</p>
                    <p><strong>Modelo: </strong>Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>682</p>
                    <p><strong>Série: </strong>273963821</p>
                    <p><strong>Modelo: </strong>Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Multlease MO300</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong>718</p>
                    <p><strong>Série: </strong>r9qr1082gxh</p>
                    <p><strong>Modelo: </strong>Samsung A02s</p>
                    <p><strong>IMEI 1: </strong>355473601371420/01</p>
                    <p><strong>IMEI 2: </strong>359454181371424/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/ana%20caroline%20alves.jpg" alt="foto de Ana Caroline de Rezende Alves" class="profile-pic">
            <h3>👤 Ana Caroline de Rezende Alves</h3>
            <p><strong>Cargo: </strong>Consultora de Operações</p>
            <p><strong>Departamento: </strong>Consultoria</p>
            <p><strong>E-mail: </strong>anacaroline.alves@amorsaude.com</p>
            <p><strong>CPF: </strong>115.334.276-66</p>
            <p><strong>Centro de Custo: </strong>12001 - Regional 1</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>461</p>
                    <p><strong>Série: </strong>GD3KZ3</p>
                    <p><strong>Modelo: </strong>Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>926</p>
                    <p><strong>Série: </strong>2332ME01XK88</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong>462</p>
                    <p><strong>Série: </strong>2232CE1CFB78 - 2230LZX1R9E8</p>
                    <p><strong>Modelo: </strong>Logitech K270 - M185</p>
                </div>
                <div class="device">
                    <h4>📌 Suporte</h4>
                    <p><strong>Patrimônio: </strong>927</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio:</strong>051</p>
                    <p><strong>Série:</strong>HVG3C03</p>
                    <p><strong>Modelo:</strong>Dell P2018HC</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/ana%20almeida.jpg" alt="Foto de Ana Clara Lourenço de Almeida" class="profile-pic">
            <h3>Ana Clara Lourenco de Almeida</h3>
            <p><strong>Cargo: </strong>Auxiliar de Pessoas & Cultura</p>
            <p><strong>Departamento: </strong>Remuneração e Benefícios</p>
            <p><strong>E-mail: </strong>ana.almeida@amorsaude.com</p>
            <p><strong>CPF: </strong> 483.778.638-39</p>
            <p><strong>Centro de Custo: </strong> 13007 - Departamento Pessoal</p>
            <p><strong>Substituição: </strong> Isabella Biscaro Vieira De Sousa</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 2369</p>
                    <p><strong>Série: </strong> PE0CMYMG</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3 IAP</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1464</p>
                    <p><strong>Série: </strong> 2334CE2DE758</p>
                    <p><strong>Modelo: </strong> MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 978</p>
                    <p><strong>Série: </strong> 2330AYO43088</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte de Alumínio para Notebook</h4>
                    <p><strong>Patrimônio: </strong> 984</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/ana%20nunes.jpg" alt="Foto de Ana Claudia Ferreira Nunes" class="profile-pic">
            <h3>👤 Ana Claudia Ferreira Nunes</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> ana.nunes@amorsaude.com</p>
            <p><strong>CPF: </strong> 362.982.368-80</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 007</p>
                    <p><strong>Série: </strong> DMJLLH3</p>
                    <p><strong>Modelo: </strong> Dell Inspiron 3501</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio:</strong>1012</p>
                    <p><strong>Série:</strong>2316ME027D59</p>
                    <p><strong>Modelo:</strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado</h4>
                    <p><strong>Patrimônio: </strong> 252</p>
                    <p><strong>Série: </strong> 2232CE1CC278</p>
                    <p><strong>Modelo: </strong> Logitech K270</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 252</p>
                    <p><strong>Série: </strong> 2232CE1CC278</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 453</p>
                    <p><strong>Série: </strong> 307AZNK97767</p>
                    <p><strong>Modelo: </strong> LG 22MP410</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte de Alumínio para Notebook</h4>
                    <p><strong>Patrimônio: </strong> 905</p>
                    <p><strong>Série: </strong> (Não informado)</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio:</strong>628</p>
                    <p><strong>Série:</strong>R9QTA00GH5A</p>
                    <p><strong>Modelo:</strong>Galaxy A03</p>
                    <p><strong>IMEI 1:</strong>351989279290265/01</p>
                    <p><strong>IMEI 2:</strong>353297239280260/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/ana%20hermogenes.jpg" alt="Foto de Ana Cláudia Mata de Sousa Hermogenes" class="profile-pic">
            <h3>👤 Ana Cláudia Mata de Sousa Hermogenes</h3>
            <p><strong>Cargo: </strong> Assistente de Retenção</p>
            <p><strong>Departamento: </strong> Relacionamento com Cliente / Retenção</p>
            <p><strong>E-mail: </strong> ana.hermogenes@amorsaude.com</p>
            <p><strong>CPF: </strong> 313.083.518-01</p>
            <p><strong>Centro de Custo: </strong> 72023 - Relacionamento com Cliente / Retenção</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1503</p>
                    <p><strong>Série: </strong> F3Y3F14</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado</h4>
                    <p><strong>Patrimônio: </strong> 203</p>
                    <p><strong>Série: </strong> JNZYR0036</p>
                    <p><strong>Modelo: </strong> Logitech K235</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 203</p>
                    <p><strong>Série: </strong> JNZYR0036</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 989</p>
                    <p><strong>Série: </strong> 2330AY02D1AB</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 987</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/ana%20julia%20bujardi.jpg" alt="Foto de Ana Julia Bujardi" class="profile-pic">
            <h3>👤 Ana Julia Bujardi</h3>
            <p><strong>Cargo: </strong> Analista de Administração de Pessoal</p>
            <p><strong>Departamento: </strong> Administração de Pessoal</p>
            <p><strong>E-mail: </strong> ana.bujardi@amorsaude.com</p>
            <p><strong>CPF: </strong> 451.239.538-50</p>
            <p><strong>Centro de Custo: </strong> 13007 - Departamento Pessoal</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1454</p>
                    <p><strong>Série: </strong> 8V9VD14</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 972</p>
                    <p><strong>Série: </strong> 2330AY031F68</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado</h4>
                    <p><strong>Patrimônio: </strong> 1453</p>
                    <p><strong>Série: </strong> 2334CE2DA8B8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1453</p>
                    <p><strong>Série: </strong> 2331LXRRGP8</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 354</p>
                    <p><strong>Série: </strong> 302AZDB7F874</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte de Alumínio para Notebook</h4>
                    <p><strong>Patrimônio: </strong> 958</p>
                    <p><strong>Série: </strong> (Não informado)</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto de Ana Laura Santos" class="profile-pic">
            <h3>👤 Ana Laura Ferrari Maila Santos</h3>
            <p><strong>Cargo: </strong> Assistente Administronline</p>
            <p><strong>Departamento: </strong> Amor Cirurgias</p>
            <p><strong>E-mail: </strong> analaura.santos@amorsaude.com</p>
            <p><strong>CPF: </strong> 451.414.178-08</p>
            <p><strong>Centro de Custo: </strong> 32001 - Administronline</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 168</p>
                    <p><strong>Série: </strong> PE097838</p>
                    <p><strong>Modelo: </strong> Lenovo Ideapd3 15alc6</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 595</p>
                    <p><strong>Série: </strong> 26438595</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 585</p>
                    <p><strong>Série: </strong> r9xt703hlgw</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 354985989158872/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/ana%20fumagalli.jpg" alt="Foto de Ana Luisa Rezende Machado Fumagalli" class="profile-pic">
            <h3>👤 Ana Luisa Fumagalli</h3>
            <p><strong>Cargo: </strong> Coordenadora de Backoffice</p>
            <p><strong>Departamento: </strong> Gerencia Operações</p>
            <p><strong>E-mail: </strong> analuisa.fumagalli@amorsaude.com</p>
            <p><strong>CPF: </strong> 430.792.958-62</p>
            <p><strong>Centro de Custo: </strong> 12005 - BackOffice</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 257</p>
                    <p><strong>Série: </strong> CNZ64R3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado</h4>
                    <p><strong>Patrimônio: </strong> 1553</p>
                    <p><strong>Série: </strong> 2347CE268028</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1553</p>
                    <p><strong>Série: </strong> 2347CE268028</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/ana%20costa.jpg" alt="Foto de Ana Luiz Bernardes Peres Costa" class="profile-pic">
            <h3>👤 Ana Luiz Bernardes Peres Costa</h3>
            <p><strong>Cargo: </strong> Assistente de Cadastro</p>
            <p><strong>Departamento: </strong> Cadastro</p>
            <p><strong>E-mail: </strong> ana.costa@amorsaude.com</p>
            <p><strong>CPF: </strong> 401.886.978-96</p>
            <p><strong>Centro de Custo: </strong> 72021 - Cadastro</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 487</p>
                    <p><strong>Série: </strong> CJHJHZ3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado</h4>
                    <p><strong>Patrimônio: </strong> 327</p>
                    <p><strong>Série: </strong> 2240LZX5J3C8</p>
                    <p><strong>Modelo: </strong> Logitech K235</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 708</p>
                    <p><strong>Série: </strong> 2250CE0D2709</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 908</p>
                    <p><strong>Série: </strong> 2332ME0207P8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 918</p>
                    <p><strong>Série: </strong> (Não informado)</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/ana%20paula%20romualdo.jpg" alt="Foto de Ana Paula Romualdo" class="profile-pic">
            <h3>👤 Ana Paula Romualdo</h3>
            <p><strong>Cargo: </strong> Assistente de Canais Digitais e Relacionamento</p>
            <p><strong>Departamento: </strong> Telemedicina / Administronline</p>
            <p><strong>E-mail: </strong> anapaula.romualdo@amorsaude.com</p>
            <p><strong>CPF: </strong> 477.406.758-06</p>
            <p><strong>Centro de Custo: </strong> 22001 - Consulta Assistida</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 310</p>
                    <p><strong>Série: </strong> CVZHNY3</p>
                    <p><strong>Modelo: </strong> Dell Latitude 3420</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 884</p>
                    <p><strong>Série: </strong> 2316ME026F9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte de Alumínio para Notebook</h4>
                    <p><strong>Patrimônio: </strong> 883</p>
                    <p><strong>Série: </strong> (Não informado)</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado</h4>
                    <p><strong>Patrimônio: </strong> 309</p>
                    <p><strong>Série: </strong> SPC2113001145</p>
                    <p><strong>Modelo: </strong> Philips C501</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 309</p>
                    <p><strong>Série: </strong> SPC2113001145</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/andre%20medina.jpg" alt="Foto de Andre Medina" class="profile-pic">
            <h3>👤 Andre Luiz Medina De Andrade</h3>
            <p><strong>Cargo: </strong> Gerente Regional De Operações</p>
            <p><strong>Departamento: </strong> Dir. Operações</p>
            <p><strong>E-mail: </strong> andre.medina@amorsaude.com</p>
            <p><strong>CPF: </strong> 270.517.138-09</p>
            <p><strong>Centro de Custo: </strong> 12000 - Dir. Operações</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 210</p>
                    <p><strong>Série: </strong> PE09XVKV</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G2</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 643</p>
                    <p><strong>Série: </strong> 273749472</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 592</p>
                    <p><strong>Série: </strong> R9XT7009JW</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 35498588534719/01</p>
                    <p><strong>IMEI 2: </strong> 355106278534710/01</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado</h4>
                    <p><strong>Patrimônio: </strong> 162</p>
                    <p><strong>Série: </strong> 2250CE0D2799</p>
                    <p><strong>Modelo: </strong> Logitech MK235</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 699</p>
                    <p><strong>Série: </strong> 2240LZX5N118</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 700</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🔌 Adaptador USB-C</h4>
                    <p><strong>Patrimônio: </strong> 965</p>
                    <p><strong>Série: </strong> 70336</p>
                    <p><strong>Modelo: </strong> Ugreen HUB USB</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/andre%20rigobello.jpg" alt="Foto de André Rigobello Pinto" class="profile-pic">
            <h3>👤 André Rigobello Pinto</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> andre.rigobello@amorsaude.com</p>
            <p><strong>CPF: </strong> 343.695.638-44</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1434</p>
                    <p><strong>Série: </strong> 31AZDB60146</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 986</p>
                    <p><strong>Série: </strong> ZL73239K6P</p>
                    <p><strong>Modelo: </strong> Moto E13</p>
                    <p><strong>IMEI 1: </strong> 355569154458892</p>
                    <p><strong>IMEI 2: </strong> 355569154458900</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 982</p>
                    <p><strong>Série: </strong> F5DY014</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 963</p>
                    <p><strong>Série: </strong> 2330AY31E18</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte de Alumínio para Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1471</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado</h4>
                    <p><strong>Patrimônio: </strong> 1020</p>
                    <p><strong>Série: </strong> 2232CE1C7118</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1020</p>
                    <p><strong>Série: </strong> 2232CE1C7118</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/andreia%20lopes.jpg" alt="Foto de Andreia Lopes" class="profile-pic">
            <h3>👤 Andreia dos Anjos Lopes</h3>
            <p><strong>Cargo: </strong> Coordenadora Financeiro</p>
            <p><strong>Departamento: </strong> Financeiro</p>
            <p><strong>E-mail: </strong> andreia.lopes@amorsaude.com</p>
            <p><strong>CPF: </strong> 416.398.198-60</p>
            <p><strong>Centro de Custo: </strong> 13001 - Financeiro</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 206</p>
                    <p><strong>Série: </strong> PE09EEK5</p>
                    <p><strong>Modelo: </strong> Lenovo V15 82ME</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 632</p>
                    <p><strong>Série: </strong> 00254425932</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 631</p>
                    <p><strong>Série: </strong> R9QTA00JMHP</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 35198279313463/01</p>
                    <p><strong>IMEI 2: </strong> 353297239323468/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/angelica%20pires.jpg" alt="Foto de Angelica Emanuela dos Santos Pires" class="profile-pic">
            <h3>👤 Angelica Emanuela dos Santos Pires</h3>
            <p><strong>Cargo: </strong> Assistente de CS</p>
            <p><strong>Departamento: </strong> SAC</p>
            <p><strong>E-mail: </strong> angelica.pires@amorsaude.com</p>
            <p><strong>CPF: </strong> 351.200.428-80</p>
            <p><strong>Centro de Custo: </strong> 12002 - SAC</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 639</p>
                    <p><strong>Série: </strong> R9XT9071V2Z</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 209</p>
                    <p><strong>Série: </strong> PE09XVKK</p>
                    <p><strong>Modelo: </strong> Lenovo V15 82ME</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 915</p>
                    <p><strong>Série: </strong> 2232ME0208J8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado</h4>
                    <p><strong>Patrimônio: </strong> 349</p>
                    <p><strong>Série: </strong> 2234CE182249</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 350</p>
                    <p><strong>Série: </strong> 130LZN0ZWZ8</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte de Alumínio para Notebook</h4>
                    <p><strong>Patrimônio: </strong> 333</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/ariane%20herrera.jpg" alt="Foto de Ariane Herrera" class="profile-pic">
            <h3>👤 Ariane Alves Herrera</h3>
            <p><strong>Cargo: </strong> Analista Administrativo SR</p>
            <p><strong>Departamento: </strong> Operações Plano</p>
            <p><strong>E-mail: </strong> ariane.herrera@amorsaude.com</p>
            <p><strong>CPF: </strong> 395.936.898-42</p>
            <p><strong>Centro de Custo: </strong> 72024 - Operações Plano</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1024</p>
                    <p><strong>Série: </strong> 2432AYN0H0P8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte de Alumínio para Notebook</h4>
                    <p><strong>Patrimônio: </strong> 910</p>
                    <p><strong>Série: </strong> (Não informado)</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado</h4>
                    <p><strong>Patrimônio: </strong> 496</p>
                    <p><strong>Série: </strong> 2323LZX5KR88</p>
                    <p><strong>Modelo: </strong> Logitech M185 MK270</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 496</p>
                    <p><strong>Série: </strong> 2325CE1138048</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 498</p>
                    <p><strong>Série: </strong> JJHJHZ3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 1204</p>
                    <p><strong>Série: </strong> ZF5244C2TJ</p>
                    <p><strong>Modelo: </strong> Moto E22</p>
                    <p><strong>IMEI 1: </strong> 352800824235995</p>
                    <p><strong>IMEI 2: </strong> 352800824236001</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/arnaldo%20junior.jpg" alt="Foto de Arnaldo Rocha Junior" class="profile-pic">
            <h3>👤 Arnaldo Rocha Junior</h3>
            <p><strong>Cargo: </strong> Gerente Contábil</p>
            <p><strong>Departamento: </strong> Contabilidade</p>
            <p><strong>E-mail: </strong> arnaldo.junior@amorsaude.com</p>
            <p><strong>CPF: </strong> 252.705.628-80</p>
            <p><strong>Centro de Custo: </strong> 13003 - Contábil</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1544</p>
                    <p><strong>Série: </strong> PE0C4SLQ</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3 IAP</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1242</p>
                    <p><strong>Série: </strong> 2316GG00ASR8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte de Alumínio para Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1243</p>
                    <p><strong>Série: </strong> (Não informado)</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado</h4>
                    <p><strong>Patrimônio: </strong> 1543</p>
                    <p><strong>Série: </strong> 2347CE268008</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1543</p>
                    <p><strong>Série: </strong> 2347CE268008</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/artur%20mello.jpg" alt="Foto de Artur Ferreira Morais Mello" class="profile-pic">
            <h3>👤 Artur Ferreira Morais Mello</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> artur.mello@amorsaude.com</p>
            <p><strong>CPF: </strong> 397.945.948-90</p>
            <p><strong>Centro de Custo: </strong> 12006 - Regional 1</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1113</p>
                    <p><strong>Série: </strong> 2421AY01PGZ8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 452</p>
                    <p><strong>Série: </strong> 308AZXC8A218</p>
                    <p><strong>Modelo: </strong> LG 22MP410</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 381</p>
                    <p><strong>Série: </strong> HRFQXW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 828</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 1031</p>
                    <p><strong>Série: </strong> SVN8D86404</p>
                    <p><strong>Modelo: </strong> Moto E13</p>
                    <p><strong>IMEI 1: </strong> 359340981262667</p>
                    <p><strong>IMEI 2: </strong> 359340981262659</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado</h4>
                    <p><strong>Patrimônio: </strong> 460</p>
                    <p><strong>Série: </strong> 2232CE1C7278</p>
                    <p><strong>Modelo: </strong> K270</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 460</p>
                    <p><strong>Série: </strong> 2227LZX8GVS8</p>
                    <p><strong>Modelo: </strong> M185</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto de Barbara Carvalho" class="profile-pic">
            <h3>👤 Barbara Formentti Carvalho</h3>
            <p><strong>Cargo: </strong> Analista de Processos e Qualidade</p>
            <p><strong>Departamento: </strong> Customer Success</p>
            <p><strong>E-mail: </strong> barbara.carvalho@amorsaude.com</p>
            <p><strong>CPF: </strong> 399.157.218-46</p>
            <p><strong>Centro de Custo: </strong> 12001 - Customer Success</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 77</p>
                    <p><strong>Série: </strong> 4NVMKW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 153510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 692</p>
                    <p><strong>Série: </strong> 275336253</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 694</p>
                    <p><strong>Série: </strong> R9XTA0EGANI</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 350916877029812/01</p>
                    <p><strong>IMEI 2: </strong> 354554237029810/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/barbara%20bidurin.jpg" alt="Foto de Barbara Bidurin" class="profile-pic">
            <h3>👤 Barbara Bidurin</h3>
            <p><strong>Cargo: </strong> Assistente Suprimentos</p>
            <p><strong>Departamento: </strong> Financeiro</p>
            <p><strong>E-mail: </strong> barbara.bidurin@amorsaude.com</p>
            <p><strong>CPF: </strong> 364.431.038-66</p>
            <p><strong>Centro de Custo: </strong> 13001 - Financeiro</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1462</p>
                    <p><strong>Série: </strong> 9V9VD14</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte de Alumínio para Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1233</p>
                    <p><strong>Série: </strong> (Não informado)</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado</h4>
                    <p><strong>Patrimônio: </strong> 1460</p>
                    <p><strong>Série: </strong> (Não informado)</p>
                    <p><strong>Modelo: </strong> Dell KM5221W</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1460</p>
                    <p><strong>Série: </strong> (Não informado)</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 721</p>
                    <p><strong>Série: </strong> R9XR500MWLZ</p>
                    <p><strong>Modelo: </strong> Galaxy A10s</p>
                    <p><strong>IMEI 1: </strong> 350879696004822</p>
                    <p><strong>IMEI 2: </strong> 359753826004823</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/beatriz%20alves.jpg" alt="Foto de Beatriz de Aguiar Alves" class="profile-pic">
            <h3>👤 Beatriz de Aguiar Alves</h3>
            <p><strong>Cargo: </strong> Assistente de Cadastro</p>
            <p><strong>Departamento: </strong> Telemedicina</p>
            <p><strong>E-mail: </strong> beatriz.alves@amorsaude.com</p>
            <p><strong>CPF: </strong> 437.698.308-06</p>
            <p><strong>Centro de Custo: </strong> 22001 - Consulta Assistida</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 920</p>
                    <p><strong>Série: </strong> 2316ME02F8B8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 58</p>
                    <p><strong>Série: </strong> P90F002</p>
                    <p><strong>Modelo: </strong> Dell Inspiron 15</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte de Alumínio para Notebook</h4>
                    <p><strong>Patrimônio: </strong> 365</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 463</p>
                    <p><strong>Série: </strong> 306AZMG9P675</p>
                    <p><strong>Modelo: </strong> LG 22MP410</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado</h4>
                    <p><strong>Patrimônio:</strong>496</p>
                    <p><strong>Série:</strong>2323LZX5KR88 - </p>
                    <p><strong>Modelo:</strong>Logitech K270</p>
                </div>
                <div class="device">
                    <h4>🖱️Mouse</h4>
                    <p><strong>Patrimônio:</strong>496</p>
                    <p><strong>Série:</strong>2325CE1138048</p>
                    <p><strong>Modelo:</strong>Logitech M185</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/beatriz%20moreira.jpg" alt="Foto de Beatriz Olive Pinho Moreira" class="profile-pic">
            <h3>👤 Beatriz Olive Pinho Moreira</h3>
            <p><strong>Cargo: </strong> Estagiária</p>
            <p><strong>Departamento: </strong> Pessoas & Cultura</p>
            <p><strong>E-mail: </strong> beatriz.moreira@amorsaude.com</p>
            <p><strong>CPF: </strong> 480.559.938-31</p>
            <p><strong>Centro de Custo: </strong> 13006 - Pessoas & Cultura</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 38</p>
                    <p><strong>Série: </strong> 2HQJS63</p>
                    <p><strong>Modelo: </strong> Dell Inspiron 15 3510</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte de Alumínio para Notebook</h4>
                    <p><strong>Patrimônio: </strong> 857</p>
                    <p><strong>Série: </strong> (Não informado)</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado</h4>
                    <p><strong>Patrimônio: </strong> 516</p>
                    <p><strong>Série: </strong> (Não informado)</p>
                    <p><strong>Modelo: </strong> Dell</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 858</p>
                    <p><strong>Série: </strong> (Não informado)</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 877</p>
                    <p><strong>Série: </strong> 2316ME027DH9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/beatriz%20chaves.jpg" alt="Foto de Beatriz Santo Chaves" class="profile-pic">
            <h3>👤 Beatriz Santo Chaves</h3>
            <p><strong>Cargo: </strong> Coordenador De Experiência Do Cliente</p>
            <p><strong>Departamento: </strong> Dir. Operações</p>
            <p><strong>E-mail: </strong> beatriz.chaves@amorsaude.com</p>
            <p><strong>CPF: </strong> 331.770.148-09</p>
            <p><strong>Centro de Custo: </strong> 12004 - Experiência do Cliente</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 188</p>
                    <p><strong>Série: </strong> PE09FTTM</p>
                    <p><strong>Modelo: </strong> Lenovo IdeaPad 3-15ALC6</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1007</p>
                    <p><strong>Série: </strong> 2350AY05D59</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado</h4>
                    <p><strong>Patrimônio: </strong> 337</p>
                    <p><strong>Série: </strong> 2234CE182199</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 338</p>
                    <p><strong>Série: </strong> 2230LZNOZNU8</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 588</p>
                    <p><strong>Série: </strong> R9XT703H6LE</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 354985898154293/01</p>
                    <p><strong>IMEI 2: </strong> 355106279154294/01</p>
                </div>
                <div class="device">
                    <h4>🔌 Adaptador de Rede</h4>
                    <p><strong>Patrimônio: </strong> 611</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Nova VOO</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/beatriz%20rezende.jpg" alt="Foto de Beatriz Vaz Rezende" class="profile-pic">
            <h3>👤 Beatriz Vaz Rezende</h3>
            <p><strong>Cargo: </strong> Especialista Hubspot</p>
            <p><strong>Departamento: </strong> CRM</p>
            <p><strong>E-mail: </strong> beatriz.rezende@amorsaude.com</p>
            <p><strong>CPF: </strong> 446.206.208-66</p>
            <p><strong>Centro de Custo: </strong> 14003 - CRM</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️ Teclado</h4>
                    <p><strong>Patrimônio: </strong> 446</p>
                    <p><strong>Série: </strong> (Não informado)</p>
                    <p><strong>Modelo: </strong> Logitech K270</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 446</p>
                    <p><strong>Série: </strong> (Não informado)</p>
                    <p><strong>Modelo: </strong> M185</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 913</p>
                    <p><strong>Série: </strong> 2329GG15AAB</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 458</p>
                    <p><strong>Série: </strong> 2316LXX3GEJ8</p>
                    <p><strong>Modelo: </strong> Vostro 3520</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/bianca%20duzzi.jpg" alt="Foto de Bianca Festucci Duzzi" class="profile-pic">
            <h3>👤 Bianca Festucci Duzzi</h3>
            <p><strong>Cargo: </strong> Analista Treinamento Jr</p>
            <p><strong>Departamento: </strong> Pessoas E Cultura</p>
            <p><strong>E-mail: </strong> bianca.duzzi@amorsaude.com</p>
            <p><strong>CPF: </strong> 484.879.158-84</p>
            <p><strong>Centro de Custo: </strong> 13006</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 307</p>
                    <p><strong>Série: </strong> 304AZZ6R809</p>
                    <p><strong>Modelo: </strong> LG MP410</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 304</p>
                    <p><strong>Série: </strong> 2243CE255CD9 - 22241LZ1UF78</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 250</p>
                    <p><strong>Série: </strong> 1SFQXW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 1024</p>
                    <p><strong>Série: </strong> R9XW20CHJVB</p>
                    <p><strong>Modelo: </strong> Galaxy A03 Core</p>
                    <p><strong>IMEI 1: </strong> 358302235393186/01</p>
                    <p><strong>IMEI 2: </strong> 359796405393182/01</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio:</strong>1023</p>
                    <p><strong>Série:</strong>2235ME00Z1H9</p>
                    <p><strong>Modelo:</strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio:</strong>850</p>
                    <p><strong>Série:</strong></p>
                    <p><strong>Modelo:</strong>Aluminio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/bianca%20inacio.jpg" alt="Foto de Bianca Inacia da Silva" class="profile-pic">
            <h3>👤 Bianca Inacia da Silva</h3>
            <p><strong>Cargo: </strong> Assistente de Operações</p>
            <p><strong>Departamento: </strong> Backoffice</p>
            <p><strong>E-mail: </strong> bianca.inacio@amorsaude.com</p>
            <p><strong>CPF: </strong> 480.094.538-03</p>
            <p><strong>Centro de Custo: </strong> 12005 - BackOffice</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 782</p>
                    <p><strong>Série: </strong> 00226785370W30K</p>
                    <p><strong>Modelo: </strong> Jabra HSC015</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 024</p>
                    <p><strong>Série: </strong> 2CKH2W2</p>
                    <p><strong>Modelo: </strong> Dell P2018HC</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 164</p>
                    <p><strong>Série: </strong> PE0973C9</p>
                    <p><strong>Modelo: </strong> Lenovo Ideapad3 15alc6</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1456</p>
                    <p><strong>Série: </strong> 2232CE1C72b8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 866</p>
                    <p><strong>Série: </strong> ZF52455HVR</p>
                    <p><strong>Modelo: </strong> Motorola E13</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1458</p>
                    <p><strong>Série: </strong> 2239CE14CECE</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/bianca%20carvalho.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Bianca Naomi de Carvalho</h3>
            <p><strong>Cargo:</strong>Desenvolvedor PL</p>
            <p><strong>Departamento:</strong>Desenvolvimento</p>
            <p><strong>E-mail:</strong>bianca.carvalho@amorsaude.com</p>
            <p><strong>CPF:</strong>484.757.418-43</p>
            <p><strong>Subtituição: </strong>Paulo Imon</p>
            <p><strong>Centro de Custo:</strong>13012 - Desenvolvimento</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio:</strong></p>
                    <p><strong>Série:</strong></p>
                    <p><strong>Modelo:</strong>Dell Vostro 15 3510 </p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio:</strong>279</p>
                    <p><strong>Série:</strong>2243CE2583F9 - 2241LZX3PAS8</p>
                    <p><strong>Modelo:</strong>Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio:</strong>838</p>
                    <p><strong>Série:</strong>2309ME013N79</p>
                    <p><strong>Modelo:</strong>LogiTech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio:</strong>829</p>
                    <p><strong>Série:</strong></p>
                    <p><strong>Modelo:</strong>Aluminio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto de Brian Cardoso Ferreira" class="profile-pic">
            <h3>👤 Brian Cardoso Ferreira</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> brian.ferreira@amorsaude.com</p>
            <p><strong>CPF: </strong> 409.024.568-06</p>
            <p><strong>Centro de Custo: </strong> 12006 - Regional 1</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 224</p>
                    <p><strong>Série: </strong> 62KLOV3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 669</p>
                    <p><strong>Série: </strong> 273964058</p>
                    <p><strong>Modelo: </strong> Jabra HSC 016</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 8</p>
                    <p><strong>Série: </strong> H3B6463</p>
                    <p><strong>Modelo: </strong> Dell P2018HC</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 677</p>
                    <p><strong>Série: </strong> R9XTA0EFPLL</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 350916877023195/01</p>
                    <p><strong>IMEI 2: </strong> 354654237023193/01</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio:</strong>819</p>
                    <p><strong>Série:</strong></p>
                    <p><strong>Modelo:</strong>Aluminio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Bruna Aliteia de Souza Branco</h3>
            <p><strong>Cargo: </strong>Analista de Parcerias Comercias Sr</p>
            <p><strong>Departamento: </strong>Cirurgias</p>
            <p><strong>E-mail: </strong>bruna.branco@amorsaude.com</p>
            <p><strong>CPF: </strong>391.294.438-57</p>
            <p><strong>Centro de Custo: </strong>32000 - Cirurgias</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>2009</p>
                    <p><strong>Série: </strong>PE0DQS47</p>
                    <p><strong>Modelo: </strong>Lenovo V15 G3 IAP</p>
                </div>
                <div class="device">
                    <h4>⌨️Teclado</h4>
                    <p><strong>Patrimônio: </strong>2000</p>
                    <p><strong>Série: </strong>4491AS-KB3121W</p>
                    <p><strong>Modelo: </strong>Dell MS3121WT</p>
                </div>
                <div class="device">
                    <h4>🖱️Mouse</h4>
                    <p><strong>Patrimônio: </strong>1110</p>
                    <p><strong>Série: </strong>4491A-MS3121W
                    <p>
                    <p><strong>Modelo: </strong>Dell MS3121WT</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>1122</p>
                    <p><strong>Série: </strong>2421AY021CH8</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong>1109</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Aluminio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong>139</p>
                    <p><strong>Série: </strong>BR-00CPG1-TVB00-9CA-1V8U-A08</p>
                    <p><strong>Modelo: </strong>Dell P2018H</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/bruna%20rosario.jpg" alt="Foto de Bruna Rosário" class="profile-pic">
            <h3>👤 Bruna de Freitas Rosário</h3>
            <p><strong>Cargo: </strong> Product Owner Jr</p>
            <p><strong>Departamento: </strong> Dental</p>
            <p><strong>E-mail: </strong> bruna.rosario@amorsaude.com</p>
            <p><strong>CPF: </strong> 442.446.868-274</p>
            <p><strong>Centro de Custo: </strong> 13013 - Produto</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio:</strong>1982</p>
                    <p><strong>Série:</strong>PE0E2HDS</p>
                    <p><strong>Modelo:</strong>Lenovo V15 G4 IRU</p>
                </div>
                <div class="device">
                    <h4>⌨️Teclado</h4>
                    <p><strong>Patrimônio:</strong>1983</p>
                    <p><strong>Série:</strong></p>
                    <p><strong>Modelo:</strong>Dell Pro KB500</p>
                </div>
                <div class="device">
                    <h4>🖱️️Mouse</h4>
                    <p><strong>Patrimônio:</strong>1395</p>
                    <p><strong>Série:</strong></p>
                    <p><strong>Modelo:</strong>Dell Pro MS300</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio:</strong>1025</p>
                    <p><strong>Série:</strong>2448MET366R8</p>
                    <p><strong>Modelo:</strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio:</strong>1078</p>
                    <p><strong>Série:</strong></p>
                    <p><strong>Modelo:</strong>Aluminio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/bruna%20reis.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Bruna Motta Carvalho dos Reis</h3>
            <p><strong>Cargo:</strong>Cordenador a Tecnica</p>
            <p><strong>Departamento:</strong>AmorLab</p>
            <p><strong>E-mail:</strong>bruna.reis@amorsaude.com</p>
            <p><strong>CPF:</strong>228.354.218-90</p>
            <p><strong>Centro de Custo:</strong>42001</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio:</strong>199</p>
                    <p><strong>Série:</strong>PE09EEMQ</p>
                    <p><strong>Modelo:</strong>Lenovo v15 82ME</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio:</strong>603</p>
                    <p><strong>Série:</strong>274054614</p>
                    <p><strong>Modelo:</strong>Jabra HSC0016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>;
                    <p><strong>Patrimônio:</strong>624</p>
                    <p><strong>Série:</strong>R9QTA00JMDH</p>
                    <p><strong>Modelo:</strong>Galaxy a03</p>
                    <p><strong>IMEI 1:</strong>351989279313422/01</p>
                    <p><strong>IMEI 2:</strong>353297239313427/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/bruna%20emilio.jpg" alt="Foto de Bruna Rafaela Emilio" class="profile-pic">
            <h3>👤 Bruna Rafaela Emilio</h3>
            <p><strong>Cargo: </strong> Consultoria de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> bruna.emilio@amorsaude.com</p>
            <p><strong>CPF: </strong> 416.119.918-03</p>
            <p><strong>Centro de Custo: </strong> 12006 - Regional 1</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 261</p>
                    <p><strong>Série: </strong> 2243CE258149</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🖇️ Suporte</h4>
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 165</p>
                    <p><strong>Série: </strong> PE09784e</p>
                    <p><strong>Modelo: </strong> Ideapad315alc6</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1010</p>
                    <p><strong>Série: </strong> 881000466</p>
                    <p><strong>Modelo: </strong> Logitech MO3000</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 794</p>
                    <p><strong>Série: </strong> RX8R90HDAAR</p>
                    <p><strong>Modelo: </strong> Galaxy AO3</p>
                    <p><strong>IMEI 1: </strong> 354397910709045/01</p>
                    <p><strong>IMEI 2: </strong> 354578130709048/01</p>
                </div>
                <div class="device">
                    <h4>🔌 Adaptador de Rede</h4>
                    <p><strong>Patrimônio: </strong> 610</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Generic</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/bruno%20dias.jpg" alt="Foto de Bruno Dias" class="profile-pic">
            <h3>👤 Bruno de Moraes Dias</h3>
            <p><strong>Cargo: </strong> Analista de Projetos Sr</p>
            <p><strong>Departamento: </strong> Projetos / Processos</p>
            <p><strong>E-mail: </strong> bruno.dias@amorsaude.com</p>
            <p><strong>CPF: </strong> 345.319.798-42</p>
            <p><strong>Centro de Custo: </strong> 13004</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🖇️ Suporte</h4>
                    <p><strong>Patrimônio: </strong> 820</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 281</p>
                    <p><strong>Série: </strong> 224Ce258419 - 2241LX3P1G8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 267</p>
                    <p><strong>Série: </strong> GP3MOT3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 831</p>
                    <p><strong>Série: </strong> 2309ME01AQW9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/bruno%20martins.jpg" alt="Foto de Bruno Martins" class="profile-pic">
            <h3>👤 Bruno Guimarães Martins</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong>Operações </p>
            <p><strong>E-mail: </strong>bruno.martins@amorsaude.com</p>
            <p><strong>CPF: </strong> 114.035.416-71</p>
            <p><strong>Centro de Custo: </strong> 12006 - Regional 1</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 225</p>
                    <p><strong>Série: </strong> PE09XVKR</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G2</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 670</p>
                    <p><strong>Série: </strong> 273963767</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 668</p>
                    <p><strong>Série: </strong> R9XTA0EDMWA</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 350916877000847/01</p>
                    <p><strong>IMEI 2: </strong> 354654237000845/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Bruno Yuri Alves De Oliveira</h3>
            <p><strong>Cargo:</strong>Desenvolvedor Sr</p>
            <p><strong>Departamento:</strong>Desenvolvimento</p>
            <p><strong>E-mail:</strong>bruno.oliveira@amorsaude.com</p>
            <p><strong>CPF:</strong>158.353.667-10</p>
            <p><strong>Subituição:</strong>William Koller</p>
            <p><strong>Centro de Custo:</strong>13012 - Desenvolvimento</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio:</strong>258</p>
                    <p><strong>Série:</strong>571781747</p>
                    <p><strong>Modelo:</strong>Dell Latitude 3420 Win-11 i5-1135G7 16GB</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/caio%20souza.jpg" alt="Foto de Caio Souza" class="profile-pic">
            <h3>👤 Caio Souza</h3>
            <p><strong>Cargo: </strong> Analista de Suporte Pl</p>
            <p><strong>Departamento: </strong> Suporte</p>
            <p><strong>E-mail: </strong> caio.souza@amorsaude.com</p>
            <p><strong>CPF: </strong>458.924.938-30</p>
            <p><strong>Centro de Custo: </strong> 13019 - Suporte</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 186</p>
                    <p><strong>Série: </strong> Jabra HSC016</p>
                    <p><strong>Modelo: </strong> Lenovo Ideapad 315alc6</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 597</p>
                    <p><strong>Série: </strong> 265438193</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 635</p>
                    <p><strong>Série: </strong> R9QA00EWMB</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 351989279272602/01</p>
                    <p><strong>IMEI 2: </strong> 35322972392260/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/caique%20picolo.jpg" alt="Foto de Caique Picolo Dal Picolo" class="profile-pic">
            <h3>👤 Caique Picolo Dal Picolo</h3>
            <p><strong>Cargo: </strong> Analista de Implantação Jr</p>
            <p><strong>Departamento: </strong> CRM</p>
            <p><strong>E-mail: </strong> caique.picolo@amorsaude.com</p>
            <p><strong>CPF: </strong> 459.427.748-97</p>
            <p><strong>Centro de Custo: </strong> 14003 - CRM</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 914</p>
                    <p><strong>Série: </strong> 2332ME01XL88</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 454</p>
                    <p><strong>Série: </strong> 1Q35KZ3</p>
                    <p><strong>Modelo: </strong> Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1534</p>
                    <p><strong>Série: </strong> 2347CE270478</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/camila%20farias.jpg" alt="Foto de Camila Bagatini Manco Farias" class="profile-pic">
            <h3>👤 Camila Bagatini Manco Farias</h3>
            <p><strong>Cargo: </strong> Consultora de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong>camila.farias@amorsaude.com</p>
            <p><strong>CPF: </strong> 218.379.668-50</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 266</p>
                    <p><strong>Série: </strong> 6Q3MOT3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 832</p>
                    <p><strong>Série: </strong> 2309ME01ANU9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 290</p>
                    <p><strong>Série: </strong> 2243CE2580E9 - 2240LZN1KEV8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 424</p>
                    <p><strong>Série: </strong> 304AZNK6R719</p>
                    <p><strong>Modelo: </strong> LG 22NP410</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 817</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/camila%20sousa.jpg" alt="Foto de Camila Cruz Alves de Sousa" class="profile-pic">
            <h3>👤 Camila Cruz Alves de Sousa</h3>
            <p><strong>Cargo: </strong> Consultoria de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong>camilla.sousa@amorsaude.com</p>
            <p><strong>CPF: </strong> 402.292.918-99</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 324</p>
                    <p><strong>Série: </strong> 5C2432ZE</p>
                    <p><strong>Modelo: </strong> Dell Vostro 153510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 880</p>
                    <p><strong>Série: </strong> 2316ME027DJ9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🖥️ Suporte</h4>
                    <p><strong>Patrimônio: </strong> 881</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 319</p>
                    <p><strong>Série: </strong> CN-0NCRCC-LO300-34C-K1FU-A01</p>
                    <p><strong>Modelo: </strong> Dell KM5221W</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 407</p>
                    <p><strong>Série: </strong> 211AZMG2V795</p>
                    <p><strong>Modelo: </strong> LG 22MP410</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/carla%20costa.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Carla Karolaine Da Costa Silvestre Silva</h3>
            <p><strong>Cargo: </strong> Coordenadora de Operações</p>
            <p><strong>Departamento: </strong> Operações</p>
            <p><strong>E-mail: </strong> carla.costa@amorsaude.com</p>
            <p><strong>CPF: </strong> 123.694.346-51</p>
            <p><strong>Centro de Custo: </strong> 18005 - Odontologia</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1429</p>
                    <p><strong>Série: </strong> D932JZ3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 949</p>
                    <p><strong>Série: </strong> 2332ME01XL68</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1428</p>
                    <p><strong>Série: </strong> 2232CE1CFB18 - 2230LZX1R8M8</p>
                    <p><strong>Modelo: </strong> Dell M185 - K270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suprote</h4>
                    <p><strong>Patrimônio: </strong> 950</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 960</p>
                    <p><strong>Série: </strong> ZFS2489MSV</p>
                    <p><strong>Modelo: </strong> Moto E13</p>
                    <p><strong>IMEI 1: </strong> 3593509814610/05</p>
                    <p><strong>IMEI 2: </strong> 3593408514628/05</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/carolina%20lopes.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Carolina Antonino Lopes</h3>
            <p><strong>Cargo: </strong> Assistente Treinamento</p>
            <p><strong>Departamento: </strong> Backoffice</p>
            <p><strong>E-mail: </strong> carolina.lopes@amorsaude.com</p>
            <p><strong>CPF: </strong> 079.460.726-86</p>
            <p><strong>Centro de Custo: </strong> 12005 - Backoffice</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 373</p>
                    <p><strong>Série: </strong> 2240CE0B599 (Teclado) / 2237LZA0B58 (Mouse)</p>
                    <p><strong>Modelo: </strong> Logitech MK235</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> N/A</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 813</p>
                    <p><strong>Série: </strong> VN-0rm97-lxn00-33f-0322-a00</p>
                    <p><strong>Modelo: </strong> Dell Wh1022</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 439</p>
                    <p><strong>Série: </strong> AX201NGW</p>
                    <p><strong>Modelo: </strong> Dell G15 5530</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/caroline%20prata%20lima.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Caroline Prata Lima</h3>
            <p><strong>Cargo: </strong> Assistente de Treinamentos</p>
            <p><strong>Departamento: </strong> Treinamentos</p>
            <p><strong>E-mail: </strong> caroline.lima@amorsaude.com</p>
            <p><strong>CPF: </strong> 083.293.926-94</p>
            <p><strong>Centro de Custo: </strong> 12005 - BackOffice</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 373</p>
                    <p><strong>Série: </strong> 2240CE0B599 (Teclado) / 2237LZA0B58 (Mouse)</p>
                    <p><strong>Modelo: </strong> Logitech MK235</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 243</p>
                    <p><strong>Série: </strong> 303AZUJ2V814</p>
                    <p><strong>Modelo: </strong> LG-22MP410-B</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> N/A</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 813</p>
                    <p><strong>Série: </strong> VN-0rm97-lxn00-33f-0322-a00</p>
                    <p><strong>Modelo: </strong> Dell Wh1022</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 439</p>
                    <p><strong>Série: </strong> AX201NGW</p>
                    <p><strong>Modelo: </strong> Dell G15 5530</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/caua%20bacilieri.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Cauã Fernando Bacilieri Silva</h3>
            <p><strong>Cargo: </strong> Estagiario</p>
            <p><strong>Departamento: </strong> BackOffice</p>
            <p><strong>E-mail: </strong> caua.bacilieri@amorsaude.com</p>
            <p><strong>CPF: </strong> 484.911.558-64</p>
            <p><strong>Centro de Custo: </strong> 12005 - BackOffice</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1479</p>
                    <p><strong>Série: </strong> 403AZAL6J088</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1548</p>
                    <p><strong>Série: </strong> PE0c4smz</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3 IAP</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1557</p>
                    <p><strong>Série: </strong> 2347CE26FD8 - 2344LZXBR4P8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1200</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1199</p>
                    <p><strong>Série: </strong> 2316GGAT09</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/cinthia%20viana.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Cinthia Cristiane Alexandre Viana</h3>
            <p><strong>Cargo: </strong> Analista Contábil Sr</p>
            <p><strong>Departamento: </strong> Contabilidade</p>
            <p><strong>E-mail: </strong> cinthia.viana@amorsaude.com</p>
            <p><strong>CPF: </strong> 425.270.208-95</p>
            <p><strong>Centro de Custo: </strong> 13001 - Financeiro</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 975</p>
                    <p><strong>Série: </strong> 2330YA0265F8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1518</p>
                    <p><strong>Série: </strong> 2345ce4ba508</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 142</p>
                    <p><strong>Série: </strong> D714sn3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1516</p>
                    <p><strong>Série: </strong> 401AZWS6F009</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1006</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/claudia%20ornela.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Claudia Matos Ornela</h3>
            <p><strong>Cargo: </strong> Analista Financeiro Jr</p>
            <p><strong>Departamento: </strong> Financeiro</p>
            <p><strong>E-mail: </strong> claudia.ornela@amorsaude.com</p>
            <p><strong>CPF: </strong> 104.448.606-65</p>
            <p><strong>Centro de Custo: </strong> 63001 - Financeiro Soluções</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 1030</p>
                    <p><strong>Série: </strong> r9xw3007pcm</p>
                    <p><strong>Modelo: </strong> Galaxy A03 Core</p>
                    <p><strong>IMEI 1: </strong> 358302235552506/01</p>
                    <p><strong>IMEI 2: </strong> 359796405555202/01</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1213</p>
                    <p><strong>Série: </strong> 2350AY051FDB</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1212</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️ Equipamento</h4>
                    <p><strong>Patrimônio: </strong> 325</p>
                    <p><strong>Série: </strong> h4ikb32121w</p>
                    <p><strong>Modelo: </strong> Dell KB3121WT</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1523</p>
                    <p><strong>Série: </strong> PE9014223078</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3</p>
                </div>
                <div class="device">
                    <h4>🔌 Carregador</h4>
                    <p><strong>Patrimônio: </strong> 1211</p>
                    <p><strong>Série: </strong> 41x6759</p>
                    <p><strong>Modelo: </strong> PA-1650-74</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1539</p>
                    <p><strong>Série: </strong> 402azvn4w311</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/claudia%20dias.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Claudia Regina Vieria Dias</h3>
            <p><strong>Cargo: </strong> Assistente Administronline</p>
            <p><strong>Departamento: </strong> Telemedicina</p>
            <p><strong>E-mail: </strong> claudia.dias@amorsaude.com</p>
            <p><strong>CPF: </strong> 162.236.378-70</p>
            <p><strong>Centro de Custo: </strong> 22001 - Consulta Assistida</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 58</p>
                    <p><strong>Série: </strong> P90F002</p>
                    <p><strong>Modelo: </strong> Dell Inspiron 3501</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 389</p>
                    <p><strong>Série: </strong> 2235ME00Z1G9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong> R9XW20CHLOF</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 358302235393566/01</p>
                    <p><strong>IMEI 2: </strong> 359796405393562/01</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 430</p>
                    <p><strong>Série: </strong> CN-0NCRCC-LO300=34C-K1H2-A01</p>
                    <p><strong>Modelo: </strong> Dell MK 5221</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 816</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 242</p>
                    <p><strong>Série: </strong> 303AZJT96660</p>
                    <p><strong>Modelo: </strong> LG 22MP410-P</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Daiana Aparecida dos Santos</h3>
            <p><strong>Cargo: </strong> Assistente de Atendimento ao Cliente</p>
            <p><strong>Departamento: </strong>Atendimento ao Cliente</p>
            <p><strong>E-mail: </strong>daiana.santos@amorsaude.com</p>
            <p><strong>CPF: </strong>366.622.218-80</p>
            <p><strong>Centro de Custo: </strong>12002 - SAC</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Marca/Modelo: </strong>Dell Vostro 15 3510</p>
                    <p class="serial"><strong>S/N: </strong>42KLov3</p>
                    <p><strong>Patrimônio: </strong>213</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Marca/Modelo: </strong>Jabra HSC016</p>
                    <p class="serial"><strong>S/N: </strong>273938593</p>
                    <p><strong>Patrimônio: </strong>648</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Marca/Modelo: </strong>Galaxy A03</p>
                    <p class="serial"><strong>S/N: </strong>r9xta0efy0t</p>
                    <p><strong>Patrimônio: </strong>659</p>
                    <p><strong>IMEI 1: </strong>350916877025638/01</p>
                    <p><strong>IMEI 2: </strong>354654237025636/01</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Marca/Modelo: </strong>Logitech MK270</p>
                    <p class="serial"><strong>S/N Teclado: </strong>2234CE182269</p>
                    <p class="serial"><strong>S/N Mouse: </strong>2230LZN10ED8</p>
                    <p><strong>Patrimônio: </strong>341 - 342</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Marca/Modelo: </strong>Alumínio</p>
                    <p class="serial"><strong>S/N: </strong>N/A</p>
                    <p><strong>Patrimônio: </strong>733</p>
                </div>
            </div>

        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/daiene%20priscila.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Daiene Priscila Alexandre</h3>
            <p><strong>Cargo: </strong> Analista de Administração Pessoal</p>
            <p><strong>Departamento: </strong> Departamento Pessoal</p>
            <p><strong>E-mail: </strong> daiene.priscila@amorsaude.com</p>
            <p><strong>CPF: </strong> 415.939.168-09</p>
            <p><strong>Centro de Custo: </strong> 13007 - Departamento Pessoal</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱�?Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 277</p>
                    <p><strong>Série: </strong> 2243CE258458</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 78</p>
                    <p><strong>Série: </strong> CNVMKW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15310</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 696</p>
                    <p><strong>Série: </strong> 275243548</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 695</p>
                    <p><strong>Série: </strong> R9XTA0BDT7D</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 352081309398711/01</p>
                    <p><strong>IMEI 2: </strong> 357299759398719/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/daila%20santos.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Daila de Souza Silva Santos</h3>
            <p><strong>Cargo: </strong> Assistente de Canais Digitais e Relacionamento</p>
            <p><strong>Departamento: </strong> Canais Digitais e Relacionamento</p>
            <p><strong>E-mail: </strong> daila.santos@amorsaude.com</p>
            <p><strong>CPF: </strong> 438.983.168-23</p>
            <p><strong>Centro de Custo: </strong> 22005 - Operação Canais Digitais</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1540</p>
                    <p><strong>Série: </strong> peoc3lr2</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3 IAP</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱�?Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1537</p>
                    <p><strong>Série: </strong> 2347ce268048</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1239</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínioo</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1240</p>
                    <p><strong>Série: </strong> 2316gg00asm8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/Daniel%20Braz.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Daniel Braz</h3>
            <p><strong>Cargo:</strong>Desenvolvedor Fullstack PL</p>
            <p><strong>Departamento:</strong>Desenvolvimento</p>
            <p><strong>E-mail:</strong>daniel.braz@amorsaude.com</p>
            <p><strong>CPF:</strong>125.722.706-84</p>
            <p><strong>Centro de Custo:</strong>13012 - Desenvolvimento</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio:</strong>1576</p>
                    <p><strong>Série:</strong>peock1k8</p>
                    <p><strong>Modelo:</strong>Lenovo v15 G3 IAP</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio:</strong>893</p>
                    <p><strong>Série:</strong></p>
                    <p><strong>Modelo:</strong>Aluminio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio:</strong>1575</p>
                    <p><strong>Série:</strong>2347CE267D98</p>
                    <p><strong>Modelo:</strong>Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio:</strong>1167</p>
                    <p><strong>Série:</strong>VN-ORMH97-LXN00-43S-07NK-AO1</p>
                    <p><strong>Modelo:</strong>Dell WH1022</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/daniel%20paula.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Daniel Oliveira Rosa de Paula</h3>
            <p><strong>Cargo: </strong> Assistente de Cadastro</p>
            <p><strong>Departamento: </strong> Dental</p>
            <p><strong>E-mail: </strong> daniel.paula@amorsaude.com</p>
            <p><strong>CPF: </strong> 441.608.948-10</p>
            <p><strong>Centro de Custo: </strong> 72021 - Cadastro</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 488</p>
                    <p><strong>Série: </strong> 8R35KZ3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 909</p>
                    <p><strong>Série: </strong> 881-000466</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 497</p>
                    <p><strong>Série: </strong> 2323LZX5TK68 - 2325CE138058</p>
                    <p><strong>Modelo: </strong> Logitech M185K270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 919</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/dariane%20romerio.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Dariane Romerio</h3>
            <p><strong>Cargo: </strong> Assistente de CS</p>
            <p><strong>Departamento: </strong> SAC</p>
            <p><strong>E-mail: </strong> dariane.romerio@amorsaude.com</p>
            <p><strong>Centro de Custo: </strong> 12002 - SAC</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 208</p>
                    <p><strong>Série: </strong> PE09XVKX</p>
                    <p><strong>Modelo: </strong> Lenovo V15 82ME</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 637</p>
                    <p><strong>Série: </strong> 273961635</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 640</p>
                    <p><strong>Série: </strong> R9XT9089maw</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                </div>
                <div class="device">
                    <h4>🖱�?Teclado e Mouse</h4>
                    <p><strong>Teclado Patrimônio: </strong> 345</p>
                    <p><strong>Mouse Patrimônio: </strong> 346</p>
                    <p><strong>Teclado Série: </strong> 2234CE182229</p>
                    <p><strong>Mouse Série: </strong> 2230LZN0ZN98</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🖇�?Suporte</h4>
                    <p><strong>Patrimônio: </strong> 331</p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Davi Ribeiro da Silva</h3>
            <p><strong>Email: </strong>davi.ribeiro@amorsaude.com</p>
            <p><strong>Cargo: </strong>Analista de dados</p>
            <p><strong>Departamento: </strong>Dados</p>
            <p><strong>CPF: </strong>417.150.938-60</p>
            <p><strong>Centro de Custo: </strong>13011 - Dados</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Marca/Modelo: </strong>Samsung Galaxy A03</p>
                    <p class="serial"><strong>S/N: </strong>R9XW20CHSQD</p>
                    <p><strong>Patrimonio: </strong>812</p>
                    <p><strong>IMEI 1: </strong>358302235395785/01</p>
                    <p><strong>IMEI 2: </strong>358302235395785/01</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Marca/Modelo: </strong>Lenovo Thinkpad e14 Gen3</p>
                    <p class="serial"><strong>S/N: </strong>PE9012117034</p>
                    <p><strong>Patrimonio: </strong>064</p>
                </div>
                <div class="device">
                    <h4>🔌 Fone</h4>
                    <p><strong>Marca/Modelo: </strong>Logitech H390</p>
                    <p class="serial"><strong>S/N: </strong>2128ME0eyna8</p>
                    <p><strong>Patrimonio: </strong>547</p>
                </div>
                <div class="device">
                    <h4>🖥�?Monitor</h4>
                    <p><strong>Marca/Modelo: </strong>Samsung F24T350FHL</p>
                    <p class="serial"><strong>S/N: </strong>Y4SKHX5T601009L</p>
                    <p><strong>Patrimonio: </strong>499</p>
                </div>
                <div class="device">
                    <h4> 🔩 Suporte</h4>
                    <p><strong>Marca/Modelo: </strong>Aluminio</p>
                    <p class="serial"><strong>S/N: </strong></p>
                    <p><strong>Patrimonio: </strong></p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Marca/Modelo: </strong>Logitech MK270</p>
                    <p class="serial"><strong>S/N: </strong>2148LZX4U4L8</p>
                    <p><strong>Patrimonio: </strong>630 - 203</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Davi Simoes dos Santos</h3>
            <p><strong>Cargo: </strong>Consultor de Operações</p>
            <p><strong>Departamento: </strong>Consultoria</p>
            <p><strong>E-mail: </strong>davi.simoes@amorsaude.com</p>
            <p><strong>CPF: </strong>456.589.498-07</p>
            <p><strong>Centro de Custo: </strong>12007 - Regional 2 </p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 047</p>
                    <p><strong>Série: </strong>PE08JFNR</p>
                    <p><strong>Modelo: </strong>Lenovo Thinkpad E14 Gen3</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 562</p>
                    <p><strong>Série: </strong>00027313312020917</p>
                    <p><strong>Modelo: </strong>Jabra HSC015</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 048</p>
                    <p><strong>Série: </strong>G01PG83</p>
                    <p><strong>Modelo: </strong>Dell Se2216H</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>;
                    <p><strong>Patrimônio: </strong> 800</p>
                    <p><strong>Série: </strong>RX8R90HDJSV</p>
                    <p><strong>Modelo: </strong>Samsung galaxy A02</p>
                    <p><strong>IMEI 1: </strong>35437310711843/01</p>
                    <p><strong>IMEI 2: </strong>354578130711846/06</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/dayane%20cristina%20silva%20dos%20santos.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Dayane Cristina Silva dos Santos</h3>
            <p><strong>Cargo: </strong>Assistente de Retenção</p>
            <p><strong>Departamento: </strong>Relacionamento com Cliente</p>
            <p><strong>E-mail: </strong>dayane.santos@amorsaude.com</p>
            <p><strong>CPF: </strong>383.111.148-03</p>
            <p><strong>Centro de Custo: </strong>72023 - Relacionamento com Cliente / Retensão</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1564</p>
                    <p><strong>Série: </strong>PE9014216040-82UM007BR-PE0C1WXT</p>
                    <p><strong>Modelo: </strong>Lenovo V15 G3 IAP</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 261</p>
                    <p><strong>Série: </strong>2243CE258148 - 2240LZN1KFFX8</p>
                    <p><strong>Modelo: </strong>Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1143</p>
                    <p><strong>Série: </strong>2412AY00NX68</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1144</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Aluminio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/deise%20leite%20dos%20santos.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Deise Leite</h3>
            <p><strong>Cargo: </strong>Consultor de Operações Internacional</p>
            <p><strong>Departamento: </strong>Internacional</p>
            <p><strong>E-mail: </strong>deise.leite@amorsaude.com</p>
            <p><strong>CPF: </strong>374.036.148-44</p>
            <p><strong>Centro de Custo: </strong>12011 - Internacional</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 488</p>
                    <p><strong>Série: </strong>8R3SK23</p>
                    <p><strong>Modelo: </strong>Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 979</p>
                    <p><strong>Série: </strong>2330AY032658</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 685</p>
                    <p><strong>Série: </strong>R9yta0c6qem</p>
                    <p><strong>Modelo: </strong>Galaxy A03</p>
                    <p><strong>IMEI 1: </strong>351084957560484/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Diego Alberto Rodrigues Blandino</h3>
            <p><strong>Cargo: </strong> Gerente De Suporte</p>
            <p><strong>Departamento: </strong> Gerência Suporte</p>
            <p><strong>E-mail: </strong> diego.blandino@amorsaude.com</p>
            <p><strong>Centro de Custo: </strong> 13015 - Gerência Dados</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 167</p>
                    <p><strong>Série: </strong> PE09782G</p>
                    <p><strong>Modelo: </strong> Ideapad3 15ALC6</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 697</p>
                    <p><strong>Série: </strong> 275340856</p>
                    <p><strong>Modelo: </strong> Jabra HSCC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 564</p>
                    <p><strong>Série: </strong> R9XT7020B3Z</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 3549859881384/01</p>
                    <p><strong>IMEI 2: </strong> 355106278871385/01</p>
                </div>
                <div class="device">
                    <h4>🖥�?Monitor</h4>
                    <p><strong>Patrimônio: </strong> 354</p>
                    <p><strong>Série: </strong> 302AZDB7874</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/dione%20antonio%20dos%20santos.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Dione Antonio dos Santos</h3>
            <p><strong>Cargo: </strong> Analista de desempenho</p>
            <p><strong>Departamento: </strong> Inteligência de negócios</p>
            <p><strong>E-mail: </strong> dione.santos@amorsaude.com</p>
            <p><strong>CPF: </strong> 470.351.168-18</p>
            <p><strong>Centro de Custo: </strong> 12010 - Inteligência de negócios</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Equipamento</h4>
                    <p><strong>Patrimônio: </strong> 40</p>
                    <p><strong>Série: </strong> j7lyxp3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 153510p</p>
                </div>
                <div class="device">
                    <h4>🎧 Equipamento</h4>
                    <p><strong>Patrimônio: </strong> 770</p>
                    <p><strong>Série: </strong> 2125me0k0568</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Equipamento</h4>
                    <p><strong>Patrimônio: </strong> 771</p>
                    <p><strong>Série: </strong> r9xr500q4ne</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A10s</p>
                    <p><strong>IMEI 1: </strong> 350879696029266/01</p>
                    <p><strong>IMEI 2: </strong> 359753826029267/01</p>
                </div>
                <div class="device">
                    <h4>⌨️ Equipamento</h4>
                    <p><strong>Patrimônio: </strong> 1482</p>
                    <p><strong>Série: </strong> 2232CE1C72C8</p>
                    <p><strong>Modelo: </strong> Logitech MK279</p>
                </div>
                <div class="device">
                    <h4>💻 Equipamento</h4>
                    <p><strong>Patrimônio: </strong> 243</p>
                    <p><strong>Série: </strong> 303AZUJ2V814</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Donato Cesar da Costa e Silva</h3>
            <p><strong>Cargo: </strong> Gerente de Customer Sucess</p>
            <p><strong>E-mail: </strong> donato.silva@amorsaude.com</p>
            <p><strong>CPF: </strong> 021.989.578-33</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 063</p>
                    <p><strong>Série: </strong> 5CVGXM3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510 p112f</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 750</p>
                    <p><strong>Série: </strong> 2125ME0JYP</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🖇�?Suporte</h4>
                    <p><strong>Modelo: </strong> ORMRME</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 717</p>
                    <p><strong>Série: </strong> R9XT20139</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 354985980029692/01</p>
                    <p><strong>IMEI 2: </strong> 355106270029693/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/ed%20pasquini.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Ed Carlos Pasquini</h3>
            <p><strong>Cargo: </strong> Coordenador De Atendimento Ao Cliente</p>
            <p><strong>Departamento: </strong> Dir. Operações</p>
            <p><strong>E-mail: </strong> ed.pasquini@amorsaude.com</p>
            <p><strong>CPF: </strong> 337.390.908-20</p>
            <p><strong>Centro de Custo: </strong> 12000 - Dir. Operações</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 170</p>
                    <p><strong>Série: </strong> Pe0973CV</p>
                    <p><strong>Modelo: </strong> Ideapad 3 15alc6</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 534</p>
                    <p><strong>Série: </strong> 263293286</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>🌐 Adaptador de Rede</h4>
                    <p><strong>Patrimônio: </strong> 558</p>
                    <p><strong>Série: </strong> 2100403922834</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 514</p>
                    <p><strong>Série: </strong> R9QT402H52A</p>
                    <p><strong>Modelo: </strong> Samsung A03</p>
                    <p><strong>IMEI 1: </strong> 3519892711808031/01</p>
                    <p><strong>IMEI 2: </strong> 353297231808036/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Edson Ortolan Junior</h3>
            <p><strong>Cargo: </strong> Assistente de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> junior.ortolan@amorsaude.com</p>
            <p><strong>CPF: </strong> 407.815.828-57</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 491</p>
                    <p><strong>Série: </strong> 2320CE162688 (Teclado)</p>
                    <p><strong>Série: </strong> 2316LZX3G0E8 (Mouse)</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 745</p>
                    <p><strong>Série: </strong> rq8rb09shfx</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A02</p>
                    <p><strong>IMEI 1: </strong> 350844817061325/01</p>
                    <p><strong>IMEI 2: </strong> 356067157061325/01</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 183</p>
                    <p><strong>Série: </strong> 7tn05l3</p>
                    <p><strong>Modelo: </strong> Dell Inspiron 3510</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 014</p>
                    <p><strong>Série: </strong> h2x5463</p>
                    <p><strong>Modelo: </strong> Dell p2018hc</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/eduardo%20junior.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Eduardo Junior</h3>
            <p><strong>Cargo: </strong> Assistente de Relacionamento com Profissionais da Saúde</p>
            <p><strong>Departamento: </strong> Relacionamento com Profissionais da Saúde</p>
            <p><strong>E-mail: </strong> eduardo.junior@amorsaude.com</p>
            <p><strong>CPF: </strong> 453.073.868-00</p>
            <p><strong>Centro de Custo: </strong> 12003 - Relacionamento Profissionais da Saúde</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 192</p>
                    <p><strong>Série: </strong> F24T350FHL</p>
                    <p><strong>Modelo: </strong> Samsung Y45KHX5T601532E</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 469</p>
                    <p><strong>Série: </strong> DG00JZ3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 484</p>
                    <p><strong>Série: </strong> 2232CCE1DO278</p>
                    <p><strong>Modelo: </strong> K270 - M185</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 939</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 940</p>
                    <p><strong>Série: </strong> 2332ME01Z138</p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/eduardo%20cheade.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Eduardo De Lima Cheade</h3>
            <p><strong>Cargo: </strong> Diretor Comercial</p>
            <p><strong>Departamento: </strong> Dir. Operações</p>
            <p><strong>E-mail: </strong> eduardo.cheade@amorsaude.com</p>
            <p><strong>CPF: </strong> 214.689.138-67</p>
            <p><strong>Centro de Custo: </strong> 32000 - Dir. Operações</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 232</p>
                    <p><strong>Série: </strong> J1KL0V3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 679</p>
                    <p><strong>Série: </strong> 00273966148</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/eduardo%20santos.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Eduardo Santos</h3>
            <p><strong>Cargo: </strong> Analista De Desempenho Pl</p>
            <p><strong>Departamento: </strong> Inteligência de Negócio</p>
            <p><strong>E-mail: </strong> eduardo.santos@amorsaude.com</p>
            <p><strong>CPF: </strong> 427.837.598-02</p>
            <p><strong>Centro de Custo: </strong> 12010 - Inteligência de Negócios</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 401</p>
                    <p><strong>Série: </strong> 303AZCQ8K059</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/eduardo%20miskulin.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Eduardo Miskulin Jardao</h3>
            <p><strong>Cargo: </strong> Product Owner Pl</p>
            <p><strong>Departamento: </strong> Produto</p>
            <p><strong>E-mail: </strong> eduardo.jardao@amorsaude.com</p>
            <p><strong>CPF: </strong> 434.023.328-50</p>
            <p><strong>Centro de Custo: </strong> 13013 - Produto</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1184</p>
                    <p><strong>Série: </strong> VN-ORMH97-LXN00-411-06XV-A01</p>
                    <p><strong>Modelo: </strong> Dell WH1022</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 079</p>
                    <p><strong>Série: </strong> PE08jfp1</p>
                    <p><strong>Modelo: </strong> Lenovo Thinkpad E14 Gen3</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 069</p>
                    <p><strong>Série: </strong> 6816d83</p>
                    <p><strong>Modelo: </strong> Dell E1920HF</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/elaine%20oliveira.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Elaine Oliveira</h3>
            <p><strong>Cargo:</strong>Analista de Atendimento ao Cliente Sr.</p>
            <p><strong>Departamento:</strong>SAC</p>
            <p><strong>E-mail:</strong>elaine.oliveira@amorsaude.com</p>
            <p><strong>CPF:</strong></p>
            <p><strong>Centro de Custo:</strong>12002 - SAC</p>
            <img class="status" src="../../media/icon/on.png" alt="">
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/elisabete%20marques%20da%20silva.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Elisabete Marques da Silva</h3>
            <p><strong>Cargo: </strong> Coordenadora de Pessoas & Cultura</p>
            <p><strong>Departamento: </strong> Pessoas e Cultura</p>
            <p><strong>E-mail: </strong> elisabete.silva@amorsaude.com</p>
            <p><strong>CPF: </strong> 153.653.358-05</p>
            <p><strong>Centro de Custo: </strong> 13021 - Gerência Pessoas e Cultura</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1439</p>
                    <p><strong>Série: </strong> JB32J3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 732</p>
                    <p><strong>Série: </strong> Não disponível</p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1444</p>
                    <p><strong>Série: </strong> 312AZER60052</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 955</p>
                    <p><strong>Série: </strong> 2329GG15JMB</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 695</p>
                    <p><strong>Série: </strong> R9XTA0BDT7D</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 352081309398711/01</p>
                    <p><strong>IMEI 2: </strong> 357299759398719/01</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1440</p>
                    <p><strong>Série: </strong> 2232CE1C7298</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/elton%20severo.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Elton Severo</h3>
            <p><strong>Cargo: </strong> Assistente de Canais Digitais e Relacionamento</p>
            <p><strong>Departamento: </strong> Telemedicina</p>
            <p><strong>E-mail: </strong> elton.severo@amorsaude.com</p>
            <p><strong>Centro de Custo: </strong> 22005 - Operação canais digitais</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1551</p>
                    <p><strong>Série: </strong> PE0C4EE0</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3 IAO</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1550</p>
                    <p><strong>Série: </strong> 2347ce270458</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1244</p>
                    <p><strong>Série: </strong> 2316gg00asz8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1329</p>
                    <p><strong>Série: </strong> Não disponível</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1569</p>
                    <p><strong>Série: </strong> 402azth6r033</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/emanuela%20santos.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Emanuela Feteira Santos</h3>
            <p><strong>Cargo: </strong> Especialista Hubspot</p>
            <p><strong>Departamento: </strong> CRM</p>
            <p><strong>E-mail: </strong> emanuela.santos@amorsaude.com</p>
            <p><strong>Centro de Custo: </strong> 14003 - CRM</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 77</p>
                    <p><strong>Série: </strong> 4NVVKW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 388</p>
                    <p><strong>Série: </strong> 2235ME OOy619</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> Não disponível</p>
                    <p><strong>Série: </strong> R9XW3007nka</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 3583022335554944/01</p>
                    <p><strong>IMEI 2: </strong> 359796405554940/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/emerson%20queiroz.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Emerson Luiz Queiroz</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> emerson.queiroz@amorsaude.com</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1435</p>
                    <p><strong>Série: </strong> 312AZTH60025</p>
                    <p><strong>Modelo: </strong> LG - 22MP410</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 152 - 701</p>
                    <p><strong>Série: </strong> 2240LZX5MK98</p>
                    <p><strong>Modelo: </strong> MK235</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 729</p>
                    <p><strong>Série: </strong> Não disponível</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 047</p>
                    <p><strong>Série: </strong> PE08JFNR</p>
                    <p><strong>Modelo: </strong> Lenovo Thinkpad E14 Gen 3</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 800</p>
                    <p><strong>Série: </strong> RX8R90HDJSV</p>
                    <p><strong>Modelo: </strong> Galaxy A02</p>
                    <p><strong>IMEI 1: </strong> 354397310711843</p>
                    <p><strong>IMEI 2: </strong> 354578130711846</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>1088</p>
                    <p><strong>Série: </strong>2432AYP0GP88</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/enzo%20siqueira.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Enzo Siqueira</h3>
            <p><strong>Cargo: </strong>Analista de Suporte JR</p>
            <p><strong>Departamento: </strong>Suporte</p>
            <p><strong>E-mail: </strong>enzo.siqueira@amorsaude.com</p>
            <p><strong>CPF: </strong></p>
            <p><strong>Centro de Custo: </strong>13019 - Suporte</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>473</p>
                    <p><strong>Série: </strong>GTZHNY3</p>
                    <p><strong>Modelo: </strong>Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong>2015</p>
                    <p><strong>Teclado Série: </strong>2347CE267D78</p>
                    <p><strong>Mouse Série: </strong>2344LZXBR5H8</p>
                    <p><strong>Modelo: </strong>Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>1032</p>
                    <p><strong>Série: </strong>VN-0RMH97-LXN00-43S-07L5-A01</p>
                    <p><strong>Modelo: </strong>Dell Wh1022</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong>820</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Aluminio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/erica%20pereira%20de%20almeida.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Erica Pereira de Almeida</h3>
            <p><strong>Cargo: </strong> Assistente Financeiro</p>
            <p><strong>Departamento: </strong> Financeiro</p>
            <p><strong>E-mail: </strong> erica.almeida@amorsaude.com</p>
            <p><strong>Centro de Custo: </strong> 13001 - Financeiro</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 928</p>
                    <p><strong>Série: </strong> Não disponível</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 833</p>
                    <p><strong>Série: </strong> 2309ME013GS9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 270</p>
                    <p><strong>Série: </strong> GFQN4T3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/erica%20teodoro%20de%20oliveira%20da%20silva.jpg" alt="Foto de Erica Teodoro de Oliveira da Silva" class="profile-pic">
            <h3>👤 Erica Teodoro de Oliveira da Silva</h3>
            <p><strong>Cargo: </strong> Analista de Suporte Pleno</p>
            <p><strong>Departamento: </strong> Suporte</p>
            <p><strong>E-mail: </strong> erica.silva@amorsaude.com</p>
            <p><strong>CPF: </strong> 369.623.608-00</p>
            <p><strong>Centro de Custo: </strong> 13019 - Suporte</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 214</p>
                    <p><strong>Série: </strong> PE9014B21134</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3 AIP</p>
                </div>
                <div class="device">
                    <h4>⌨️ Teclado</h4>
                    <p><strong>Patrimônio: </strong> 1489</p>
                    <p><strong>Série: </strong> ER90162/20</p>
                    <p><strong>Modelo: </strong>Dell KB121W</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1159</p>
                    <p><strong>Série: </strong> H4IMS3121W</p>
                    <p><strong>Modelo: </strong> Dell Full Size Wireless MS300</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1064</p>
                    <p><strong>Série: </strong> 384925183</p>
                    <p><strong>Modelo: </strong> Dell WH1022</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/erica%20teodoro%20de%20oliveira%20da%20silva.jpg" alt="Foto de Erica Teodoro de Oliveira da Silva" class="profile-pic">
            <h3>👤 Estefania Camilo Brondi Paes</h3>
            <p><strong>Cargo: </strong>Analista de Atendimento ao Cliente</p>
            <p><strong>Departamento: </strong>SAC</p>
            <p><strong>E-mail: </strong>estefania.brondi@amorsaude.com</p>
            <p><strong>CPF: </strong></p>
            <p><strong>Centro de Custo: </strong>12002</p>
            <img class="status" src="../../media/icon/on.png" alt="">
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto-do-Colaborador" class="profile-pic">
            <h3>👤 Fabio Eduardo Circucisão Rafael</h3>
            <p><strong>Cargo: </strong>Analista de Suporte JR</p>
            <p><strong>Departamento: </strong>Suporte</p>
            <p><strong>E-mail: </strong>fabio.rafael@amorsaude.com</p>
            <p><strong>CPF: </strong>415.527.898-70</p>
            <p><strong>Centro de Custo: </strong>13019 - Suporte</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>186</p>
                    <p><strong>Série: </strong>PE09FTJ2</p>
                    <p><strong>Modelo: </strong>Lenovo Ideapad3 15alc6</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong></p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>597</p>
                    <p><strong>Série: </strong>265438193</p>
                    <p><strong>Modelo: </strong>Jabra HCS016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>;
                    <p><strong>Patrimônio: </strong>586</p>
                    <p><strong>Série: </strong>R9XT703H7TD</p>
                    <p><strong>Modelo: </strong>Galaxy A03</p>
                    <p><strong>IMEI 1: </strong>35495989154699/01</p>
                </div>
            </div>
        </div>

        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/fabio%20manoel%20goncalves%20d'avilla.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Fabio Manoel Goncalves D'Avilla</h3>
            <p><strong>Cargo: </strong> Desenvolvedor Fullstack Pl</p>
            <p><strong>Departamento: </strong> Desenvolvimento</p>
            <p><strong>E-mail: </strong> fabio.davilla@amorsaude.com</p>
            <p><strong>CPF: </strong> 464.572.868-79</p>
            <p><strong>Centro de Custo: </strong> 13012 - Desenvolvimento</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 440</p>
                    <p><strong>Série: </strong> BVZHNY3</p>
                    <p><strong>Modelo: </strong> Latitude 3420</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/fabiola%20quiles.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Fabiola Todesco Quiles</h3>
            <p><strong>Cargo: </strong> Gerente Adm. Plano Odontológico</p>
            <p><strong>Departamento: </strong> Odontologia</p>
            <p><strong>E-mail: </strong> fabiola.quiles@amorsaude.com</p>
            <p><strong>CPF: </strong> 310.755.438-35</p>
            <p><strong>Centro de Custo: </strong> 18006 - Odontologia - Plano</p>
            <img class="status" src="../../media/icon/on.png" alt="">
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/fabricio%20florentino.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Fabricio Baastista Florentino</h3>
            <p><strong>Cargo: </strong> Consultor De Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> fabricio.florentino@amorsaude.com</p>
            <p><strong>CPF: </strong> 311.682.658-78</p>
            <p><strong>Centro de Custo: </strong> 12006 - Regional 1</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 845</p>
                    <p><strong>Série: </strong> UJ5VC3Q4B</p>
                    <p><strong>Modelo: </strong> Motorola E13</p>
                    <p><strong>IMEI 1: </strong> 359340981289595</p>
                    <p><strong>IMEI 2: </strong> 359340981289603</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 832</p>
                    <p><strong>Série: </strong> 2309ME01ANu9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 817</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 424</p>
                    <p><strong>Série: </strong> 304AZNK6R719</p>
                    <p><strong>Modelo: </strong> LG 22MP410</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 290</p>
                    <p><strong>Série: </strong> 2243CE2580E9 - 2240LZ1KEV8</p>
                    <p><strong>Modelo: </strong> Logitech Mk270</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/felipe%20oliveira.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Felipe Candido Oliveira</h3>
            <p><strong>Cargo: </strong>Gerente Financeiro</p>
            <p><strong>Departamento: </strong>Financeiro</p>
            <p><strong>E-mail: </strong> felipe.oliveira@amorsaude.com</p>
            <p><strong>CPF: </strong></p>
            <p><strong>Centro de Custo: </strong>13001 - Financeiro</p>
            <img class="status" src="../../media/icon/on.png" alt="">
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/felipe%20fernandes.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Felipe Lemes Fernandes</h3>
            <p><strong>Cargo: </strong> Analista de Desempenho</p>
            <p><strong>Departamento: </strong> Inteligência de Negócio</p>
            <p><strong>E-mail: </strong> felipe.fernandes@amorsaude.com</p>
            <p><strong>CPF: </strong> 410.057.008-26</p>
            <p><strong>Centro de Custo: </strong> 12010 - Inteligência de Negócios</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 231</p>
                    <p><strong>Série: </strong> 12KL0v3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 806</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 043</p>
                    <p><strong>Série: </strong> 1232C03</p>
                    <p><strong>Modelo: </strong> Dell P2018HC</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 320 - 863</p>
                    <p><strong>Série: </strong> CN-ONCRCC-LO300-331-KOUK-A01</p>
                    <p><strong>Modelo: </strong> Dell MK5221W</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 856</p>
                    <p><strong>Série: </strong> 2309Me01AQY9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/Felipe%20Sacagnhe%20dos%20Santos.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Felipe Sacagnhe dos Santos</h3>
            <p><strong>Cargo: </strong>Consultora de Operações</p>
            <p><strong>Departamento: </strong>Operação</p>
            <p><strong>E-mail: </strong>felipe.santos@amorsaude.com</p>
            <p><strong>CPF: </strong>391.420.088-08</p>
            <p><strong>Centro de Custo: </strong>12007 - Regional 2</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>215</p>
                    <p><strong>Série: </strong>32KLOV3</p>
                    <p><strong>Modelo: </strong>Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong>72 - 706</p>
                    <p><strong>Série: </strong>2250CE0D0A29 - 2240LZX5M588</p>
                    <p><strong>Modelo: </strong>Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>1076</p>
                    <p><strong>Série: </strong>2423AYQ0H198</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong>704</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Aluminio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong>399</p>
                    <p><strong>Série: </strong>303AZSP94390</p>
                    <p><strong>Modelo: </strong>LG-22MP410-B</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>;
                    <p><strong>Patrimônio: </strong>661</p>
                    <p><strong>Série: </strong>r9xta0ecf7k</p>
                    <p><strong>Modelo: </strong>Samsung Galaxy A03</p>
                    <p><strong>IMEI 1: </strong>350916876987754/01</p>
                    <p><strong>IMEI 2: </strong>354654236987752/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/fernanda%20barbosa.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Fernanda Beatriz Silva Barbosa</h3>
            <p><strong>Cargo: </strong> Consultora Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> fernanda.barbosa@amorsaude.com</p>
            <p><strong>CPF: </strong> 120.426.446-50</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 228</p>
                    <p><strong>Série: </strong> B3KL0V3</p>
                    <p><strong>Modelo: </strong> Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1065</p>
                    <p><strong>Série: </strong> 2421AY01S9AB</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 71 - 705</p>
                    <p><strong>Série: </strong> 2250CE0D2769 - 2240LZX5K8u8</p>
                    <p><strong>Modelo: </strong> Logitech Mk235</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 703</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> N/A</p>
                    <p><strong>Série: </strong> R9XTA0EFW1K</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 350916877024987/01</p>
                    <p><strong>IMEI 2: </strong> 354654237024985/01</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 398</p>
                    <p><strong>Série: </strong> 303AZSP96550</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/fernanda%20espadari.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Fernanda Radiguieri Espadari</h3>
            <p><strong>Cargo: </strong> Estagiária</p>
            <p><strong>Departamento: </strong> BackOffice</p>
            <p><strong>E-mail: </strong> fernanda.espadari@amorsaude.com</p>
            <p><strong>CPF: </strong> 444.709.338-30</p>
            <p><strong>Centro de Custo: </strong> 12005 - BackOffice</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 450</p>
                    <p><strong>Série: </strong> 1SZZHZE</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 922</p>
                    <p><strong>Série: </strong> 2332ME01XKZ8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 457</p>
                    <p><strong>Série: </strong> 2230LZX1R9W8</p>
                    <p><strong>Modelo: </strong> Logitech K270 - M185</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 390</p>
                    <p><strong>Série: </strong> 303AZQU96524</p>
                    <p><strong>Modelo: </strong> LG-22MP410-P</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/fernando%20silva.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Fernando Vinicius Silva</h3>
            <p><strong>Cargo: </strong> Consultor(A) De Operações</p>
            <p><strong>Departamento: </strong> Regional 2</p>
            <p><strong>E-mail: </strong> fernando.silva@amorsaude.com</p>
            <p><strong>CPF: </strong> 373.868.358-50</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 330</p>
                    <p><strong>Série: </strong> FT2FWY3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 153510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 692</p>
                    <p><strong>Série: </strong> 2753362553</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 891</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 408</p>
                    <p><strong>Série: </strong> 2306CE122759 - 2305LZXNXV8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 538</p>
                    <p><strong>Série: </strong> R(XT2013C3L</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 35485980030500/01</p>
                    <p><strong>IMEI 2: </strong> 355106270030501/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/fabricio%20freitas.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Francisco Fabricio De Lima Freitas</h3>
            <p><strong>Cargo: </strong> Gerente Financeiro</p>
            <p><strong>Departamento: </strong> Financeiro</p>
            <p><strong>E-mail: </strong> fabricio.freitas@amorsaude.com</p>
            <p><strong>CPF: </strong> 582.444.093-04</p>
            <p><strong>Centro de Custo: </strong> 13000 - Financeiro</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1525</p>
                    <p><strong>Série: </strong> 2232ce1d0218</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 843</p>
                    <p><strong>Série: </strong> UI5HVE5P7T</p>
                    <p><strong>Modelo: </strong> Moto E22</p>
                </div>
                <div class="device">
                    <h4>🎧 Headset</h4>
                    <p><strong>Patrimônio: </strong> 600</p>
                    <p><strong>Série: </strong> 2654439793</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 189</p>
                    <p><strong>Série: </strong> PE09FTTP</p>
                    <p><strong>Modelo: </strong> Ideapad3 15AL6</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1015</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Gabriel Borges Silverio</h3>
            <p><strong>Cargo: </strong> Consultor de Operação</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong>gabriel.borges@amorsaude.com</p>
            <p><strong>CPF: </strong> 417.371.938-84</p>
            <p><strong>Centro de Custo: </strong></p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 7</p>
                    <p><strong>Série: </strong> DMJILh3</p>
                    <p><strong>Modelo: </strong> Inspiron3501 P90F</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 646</p>
                    <p><strong>Série: </strong> 273748566</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 582</p>
                    <p><strong>Série: </strong> R9XT700F6NK</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 354985988580910/01</p>
                    <p><strong>IMEI 2: </strong> 355106278590811/01</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 65</p>
                    <p><strong>Série: </strong> 3BBTQK3</p>
                    <p><strong>Modelo: </strong> Logitech</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 20</p>
                    <p><strong>Série: </strong> CB6H9Q2</p>
                    <p><strong>Modelo: </strong> Dell O2018HC</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/gabriel%20cardinali.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Gabriel Cardinali</h3>
            <p><strong>Cargo: </strong> Coordenador De Pesquisa E Desenvolvimento</p>
            <p><strong>Departamento: </strong> Dir. Financeira</p>
            <p><strong>E-mail: </strong> gabriel.cardinali@amorsaude.com</p>
            <p><strong>CPF: </strong> 393.549.008-95</p>
            <p><strong>Centro de Custo: </strong> 13000 - Dir. Financeira</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1501</p>
                    <p><strong>Série: </strong> 401AZXC5Y114</p>
                    <p><strong>Modelo: </strong> LG - 22MP410-B</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 256</p>
                    <p><strong>Série: </strong> 40NWZQ3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 642</p>
                    <p><strong>Série: </strong> 273941756</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 813</p>
                    <p><strong>Série: </strong> lzxk0dx8</p>
                    <p><strong>Modelo: </strong> Logitech M185</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 644</p>
                    <p><strong>Série: </strong> R9XT908A4AA</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 353541239444227/01</p>
                    <p><strong>IMEI 2: </strong> 354183763444227/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/gabriel%20carvalho.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Gabriel Carrasco De Carvalho</h3>
            <p><strong>Cargo: </strong> Consultor De Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> gabriel.carvalho@amorsaude.com</p>
            <p><strong>CPF: </strong> 445.240.258-55</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <p><strong>Substituição: </strong> Tharinne Pedreschi</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1436</p>
                    <p><strong>Série: </strong> 2232CE1C7158 - 2229LZXL7AB8</p>
                    <p><strong>Modelo: </strong> K270 - M185</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1152</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 715</p>
                    <p><strong>Série: </strong> 2399829109</p>
                    <p><strong>Modelo: </strong> Jabra HSC015</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 323</p>
                    <p><strong>Série: </strong> 4C2A3Z3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 810</p>
                    <p><strong>Série: </strong> R9XW20CHL4K</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 358302235393608/01</p>
                    <p><strong>IMEI 2: </strong> 359796405393604/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/gabriel%20dourado.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Gabriel Dourado</h3>
            <p><strong>Cargo:</strong>Analista De Desenvolvimento</p>
            <p><strong>Departamento:</strong>Desenvolvimento</p>
            <p><strong>E-mail:</strong>gabriel.dourado@amorsaude.com</p>
            <p><strong>CPF:</strong></p>
            <p><strong>Centro de Custo:</strong>13012 - Desenvolvimento</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio:</strong>158</p>
                    <p><strong>Série:</strong>Pe08sjca</p>
                    <p><strong>Modelo:</strong>Thinkpade14 gen3 Win-10 8GB Ryzen 5 5500u</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio:</strong>714</p>
                    <p><strong>Série:</strong>2126me00k5w8</p>
                    <p><strong>Modelo:</strong>Logitech H390</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/gabriel%20guilherme.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Gabriel Guilherme</h3>
            <p><strong>Cargo: </strong> Analista de Dados</p>
            <p><strong>Departamento: </strong> Dados</p>
            <p><strong>E-mail: </strong> gabriel.guilherme@amorsaude.com</p>
            <p><strong>CPF: </strong> 370.430.828-54</p>
            <p><strong>Centro de Custo: </strong> 13011 - Suporte</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 352</p>
                    <p><strong>Série: </strong> 303AZSP3A774</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 352</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 065</p>
                    <p><strong>Série: </strong> 3bbtqk3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3400</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 202 - 629</p>
                    <p><strong>Série: </strong> 2207lzx91308</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Gabriel Renan Gregorato Santos</h3>
            <p><strong>Cargo: </strong> Desenvolvedor Jr</p>
            <p><strong>Departamento: </strong> 04.08.2023</p>
            <p><strong>E-mail: </strong> gabriel.santos@amorsaude.com</p>
            <p><strong>CPF: </strong> 431.775.058-96</p>
            <p><strong>Centro de Custo: </strong> 13012 - Desenvolvimento</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/gabriel%20rodrigues%20de%20morais.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Gabriel Rodrigues de Morais</h3>
            <p><strong>Cargo: </strong>Gerente de Pessoas</p>
            <p><strong>Departamento: </strong>Pessoas e Cultura</p>
            <p><strong>E-mail: </strong>gabriel.morais@amorsaude.com</p>
            <p><strong>CPF: </strong>008.048.811-03</p>
            <p><strong>Centro de Custo: </strong>3029 - Dir. Pessoas e Cultura</p>
            <p><strong>Subtituição: </strong>Sandra Andreia de Oliveira</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>467</p>
                    <p><strong>Série: </strong>7972JZ3</p>
                    <p><strong>Modelo: </strong>Dell Vostro 3510</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong>2017</p>
                    <p><strong>Teclado Série: </strong>2347CE267FF8</p>
                    <p><strong>Mouse Série: </strong>2344LZXBR4Q8</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>1103</p>
                    <p><strong>Série: </strong>2421AY020QU8</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong>1031</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Aluminio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/gabriel%20nadeu.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Gabriel Rodrigues Nadeu</h3>
            <p><strong>Cargo: </strong>Coordenador(a) de Projetos e Processos</p>
            <p><strong>Departamento: </strong> Projeto/Processo</p>
            <p><strong>CPF: </strong>395.879.798-93</p>
            <p><strong>Centro de Custo: </strong>13004 - Projetos</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h3>💻Notebook</h3>
                    <p><strong>Modelo: </strong>Dell Vostro 15 3510</p>
                    <p><strong>S/N: </strong>12327755331</p>
                    <p><strong>Patrimônio: </strong>075</p>
                </div>
                <div class="device">
                    <h3>🎧Fone</h3>
                    <p><strong>Modelo: </strong>Jabra HSC016</p>
                    <p><strong>S/N: </strong>00276244184</p>
                    <p><strong>Patrimônio: </strong>690</p>
                </div>
                <div class="device">
                    <h3>⌨Teclado</h3>
                    <p><strong>Modelo: </strong>Logitech K270</p>
                    <p><strong>S/N: </strong>2232CE1D0238</p>
                    <p><strong>Patrimônio: </strong>455</p>
                </div>
                <div class="device">
                    <h3>🖱️Mouse </h3>
                    <p><strong>Modelo: </strong>Logitech M185</p>
                    <p><strong>S/N: </strong>2230LZX3BNG8</p>
                    <p><strong>Patrimônio: </strong>1162</p>
                </div>
                <div class="device">
                    <h3>🔩Suporte</h3>
                    <p><strong>Modelo: </strong>Aluminio</p>
                    <p><strong>S/N: </strong></p>
                    <p><strong>Patrimônio: </strong>2251</p>
                </div>
                <div class="device">
                    <h3>🖥️ Monitor</h3>
                    <p><strong>Patrimônio: </strong>395</p>
                    <p><strong>Série: </strong>303AZZX6K061</p>
                    <p><strong>Modelo: </strong>LG-22MP410-B</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/gabriela%20martins.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Gabriela Bouchabki Martins</h3>
            <p><strong>Cargo: </strong> Product Owner</p>
            <p><strong>Departamento: </strong> Produtos</p>
            <p><strong>E-mail: </strong> gabriela.martins@amorsaude.com</p>
            <p><strong>CPF: </strong> 346.189.228-93</p>
            <p><strong>Centro de Custo: </strong> 13013 - Produto</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 079</p>
                    <p><strong>Série: </strong> PE08jfp1</p>
                    <p><strong>Modelo: </strong> Lenovo Thinkpad E14 Gen3</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 738</p>
                    <p><strong>Série: </strong> 00205867021</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 069</p>
                    <p><strong>Série: </strong> 6816d83</p>
                    <p><strong>Modelo: </strong> Dell E1920HF</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/Gabriela%20Cardoso%20Martins.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Gabriela Cardoso Martins</h3>
            <p><strong>Cargo: </strong>Product Owner PL</p>
            <p><strong>Departamento: </strong>Projetos / Processos</p>
            <p><strong>E-mail: </strong>gabriela.cardoso@amorsaude.com</p>
            <p><strong>CPF: </strong>341.371.458-90</p>
            <p><strong>Centro de Custo: </strong>13004 - Projetos / Processos</p>
            <p class="offline"><span>Status: Offline</span></p>
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>267</p>
                    <p><strong>Série: </strong>GP3MOT3</p>
                    <p><strong>Modelo: </strong>Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong>281 </p>
                    <p><strong>Série: </strong>224Ce258419 - 2241LX3P1G8</p>
                    <p><strong>Modelo: </strong>Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>831</p>
                    <p><strong>Série: </strong>2309ME01AQW9</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong>Aluminio</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>820</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/gabriela%20fritz.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Gabriela Fritz</h3>
            <p><strong>Cargo: </strong> Analista de Experiência do Cliente</p>
            <p><strong>Departamento: </strong> Experiência do Cliente</p>
            <p><strong>E-mail: </strong> gabriela.fritz@amorsaude.com</p>
            <p><strong>CPF: </strong> 446.392.818-46</p>
            <p><strong>Centro de Custo: </strong> 12004 - Experiência do Cliente</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 211</p>
                    <p><strong>Série: </strong> PE09XVKT</p>
                    <p><strong>Modelo: </strong> Lenovo V15 V2</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 421</p>
                    <p><strong>Série: </strong> CN-ONRCC-LO300-331K14W-A01</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 868</p>
                    <p><strong>Série: </strong> 2315AY078409</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 485</p>
                    <p><strong>Série: </strong> 308AZW585585</p>
                    <p><strong>Modelo: </strong> LG22M410</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 564</p>
                    <p><strong>Série: </strong> R9XT7020B3Z</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 35510627887138501</p>
                    <p><strong>IMEI 2: </strong> 35498598887138401</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/gabriela%20cristina.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Gabriela Cristina Soares</h3>
            <p><strong>Cargo: </strong> Assistente de Operações</p>
            <p><strong>Departamento: </strong> Backoffice</p>
            <p><strong>E-mail: </strong> gabriela.cristina@amorsaude.com</p>
            <p><strong>CPF: </strong> 449.273.438-47</p>
            <p><strong>Centro de Custo: </strong> 12005 - Backoffice</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 105</p>
                    <p><strong>Série: </strong> PE08znfj</p>
                    <p><strong>Modelo: </strong> Lenovo IdeaPad 315ALC6</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 906</p>
                    <p><strong>Série: </strong> 23298GG15AB8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1456</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1151</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 409</p>
                    <p><strong>Série: </strong> 304AZZX6Z237</p>
                    <p><strong>Modelo: </strong> LG 22MP410</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 1027</p>
                    <p><strong>Série: </strong> R9XW103EH6L</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 3583302232682789/01</p>
                    <p><strong>IMEI 2: </strong> 359796402682785/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/gabriela%20georgete.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Gabriela Georgete da Cruz</h3>
            <p><strong>Cargo: </strong> Analista Financeiro</p>
            <p><strong>Departamento: </strong> Financeiro</p>
            <p><strong>E-mail: </strong> gabriela.georgete@amorsaude.com</p>
            <p><strong>CPF: </strong> 483.921.198-98</p>
            <p><strong>Centro de Custo: </strong> 13001 - Financeiro</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1461</p>
                    <p><strong>Série: </strong> 401AZYE6G026</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 032</p>
                    <p><strong>Série: </strong> P9OF005</p>
                    <p><strong>Modelo: </strong> Dell Inspiron 3501</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 587</p>
                    <p><strong>Série: </strong> r9xt700tzmv</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 354985988720169/01</p>
                    <p><strong>IMEI 2: </strong> 3551062787201601</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 289</p>
                    <p><strong>Série: </strong> 2243CE25D59</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 821</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/gabriele%20marin%20soare.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Gabriele Marin Soare</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> gabriele.marin@amorsaude.com</p>
            <p><strong>CPF: </strong> 453.937.658-60</p>
            <p><strong>Centro de Custo: </strong> 12006 - Regional 1</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 374</p>
                    <p><strong>Série: </strong> 2240CE0B54A9 - 2237LZXA0BL8</p>
                    <p><strong>Modelo: </strong> Logitech MK235</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 366</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 221</p>
                    <p><strong>Série: </strong> 23KLOV3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 664</p>
                    <p><strong>Série: </strong> R9XTA0eGK5T</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 3591687703618/01</p>
                    <p><strong>IMEI 2: </strong> 35465423702616/01</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 391</p>
                    <p><strong>Série: </strong> 303AZSP96526</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/Gabrielly%20Simoes.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Gabrielly Mendes Simões</h3>
            <p><strong>Cargo: </strong>Estagiária de Canais Digitais e Relacionamento</p>
            <p><strong>Departamento: </strong>Telemedicina</p>
            <p><strong>E-mail: </strong>gabrielly.simoes@amorsaude.com</p>
            <p><strong>CPF: </strong>459.695.128-42</p>
            <p><strong>Centro de Custo: </strong>22001 - Consulta Assistida</p>
            <p><strong>Subtituição: </strong>Lucas Guidetti Rocha</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>449</p>
                    <p><strong>Série: </strong>4J32JZ3</p>
                    <p><strong>Modelo: </strong>Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong>456</p>
                    <p><strong>Série: </strong>2230LZX1R868</p>
                    <p><strong>Modelo: </strong>Logitech K270 - M185</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>954</p>
                    <p><strong>Série: </strong>2323GGO15J98</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong>935</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Alumnio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong>242</p>
                    <p><strong>Série: </strong>232360GG07</p>
                    <p><strong>Modelo: </strong>LG 22MP410-B</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Gilmara Luiza de Oliveira</h3>
            <p><strong>Cargo:</strong>Assistente de Atendimento ao Cliente</p>
            <p><strong>Departamento:</strong>Atendimento ao Cliente</p>
            <p><strong>E-mail:</strong>gilmara.oliveira@amorsaude.com</p>
            <p><strong>CPF:</strong> 406.568.658-06</p>
            <p><strong>Centro de Custo:</strong>12002 - SAC</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio:</strong>1060</p>
                    <p><strong>Série:</strong>2448ME6365R8</p>
                    <p><strong>Modelo:</strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio:</strong>1019</p>
                    <p><strong>Série:</strong></p>
                    <p><strong>Modelo:</strong>Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio:</strong>1529</p>
                    <p><strong>Série:</strong>2345CE4BA518</p>
                    <p><strong>Modelo:</strong>Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>;
                    <p><strong>Patrimônio:</strong>1228</p>
                    <p><strong>Série:</strong>ZF5244C2KF</p>
                    <p><strong>Modelo:</strong>Moto E22</p>
                    <p><strong>IMEI 1:</strong>352800824226754</p>
                    <p><strong>IMEI 2:</strong>352800824226762</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio:</strong>1477</p>
                    <p><strong>Série:</strong>403AZBZ6J261</p>
                    <p><strong>Modelo:</strong>LG-22MP410-B</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio:</strong>1528</p>
                    <p><strong>Série:</strong>PEOC4E5</p>
                    <p><strong>Modelo:</strong>Lenovo V15 G3</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/gisele%20brochetto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Gisele Brochetto</h3>
            <p><strong>Cargo: </strong> Coordenador De Relacionamento Com Prestadores De Saúde</p>
            <p><strong>Departamento: </strong> Dir. Operações</p>
            <p><strong>E-mail: </strong> gisele.brochetto@amorsaude.com</p>
            <p><strong>CPF: </strong> 368.565.628-70</p>
            <p><strong>Centro de Custo: </strong> 12000 - Dir. Operações</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> 693</p>
                    <p><strong>Série: </strong> 229lzxk31c8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/graziele%20ambrosio.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Graziele Giacon Ambrosio</h3>
            <p><strong>Cargo: </strong> Consultora de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> graziele.ambrosio@amorsaude.com</p>
            <p><strong>CPF: </strong> 217.069.478-10</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 215</p>
                    <p><strong>Série: </strong> 32KLOV3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 661</p>
                    <p><strong>Série: </strong> r9xta0ecf7k</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 350916876987754/01</p>
                    <p><strong>IMEI 2: </strong> 354654236987752/01</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 650</p>
                    <p><strong>Série: </strong> 273966069</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 72 - 706</p>
                    <p><strong>Série: </strong> 2250CE0D0A29 - 2240LZX5M588</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 704</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 399</p>
                    <p><strong>Série: </strong> 303AZSP94390</p>
                    <p><strong>Modelo: </strong> LG-22MP410-B</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/guilherme%20neto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Guilherme Neto</h3>
            <p><strong>Cargo: </strong> Analista de Relacionamento com Profissionais da Saúde</p>
            <p><strong>Departamento: </strong> Relacionamento com Profissionais da Saúde</p>
            <p><strong>E-mail: </strong> guilherme.neto@amorsaude.com</p>
            <p><strong>CPF: </strong> 459.817.408-09</p>
            <p><strong>Centro de Custo: </strong> 12003 - Relacionamento Profissionais da Saúde</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 911</p>
                    <p><strong>Série: </strong> 2332ME01XKY8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 931</p>
                    <p><strong>Série: </strong> [Informação não fornecida]</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 455</p>
                    <p><strong>Série: </strong> 2232CE1D02338</p>
                    <p><strong>Modelo: </strong> MK270</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 448</p>
                    <p><strong>Série: </strong> 35GJHZ3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/guilherme%20correa.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Guilherme Correa</h3>
            <p><strong>Cargo: </strong> Analista de Negócio</p>
            <p><strong>Departamento: </strong> Inteligência de Negócio</p>
            <p><strong>E-mail: </strong> guilherme.correa@amorsaude.com</p>
            <p><strong>CPF: </strong> N/A</p>
            <p><strong>Centro de Custo: </strong> 12010</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1573</p>
                    <p><strong>Série: </strong> HNSMC44</p>
                    <p><strong>Modelo: </strong> Dell G15</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte Notebook</h4>
                    <p><strong>Patrimônio: </strong> 806</p>
                    <p><strong>Série: </strong> [Informação não fornecida]</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 402</p>
                    <p><strong>Série: </strong> 303azws96609</p>
                    <p><strong>Modelo: </strong> LG 22Mp410-B</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 285</p>
                    <p><strong>Série: </strong> 2243CE257D79 - 2240LZN1GCL8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/heitor%20de%20paiva%20boccato.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Heitor De Paiva Boccato</h3>
            <p><strong>Cargo: </strong> Analista de Desempenho Jr</p>
            <p><strong>Departamento: </strong> Inteligência de Negócios</p>
            <p><strong>E-mail: </strong> heitor.boccato@amorsaude.com</p>
            <p><strong>CPF: </strong> 470.351.168-18</p>
            <p><strong>Centro de Custo: </strong> 12010 - Inteligência de Negócios</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1432</p>
                    <p><strong>Série: </strong> 312AZM60Z451</p>
                    <p><strong>Modelo: </strong> LG-22MP410-B</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1565</p>
                    <p><strong>Série: </strong> 8YePC44</p>
                    <p><strong>Modelo: </strong> Notebook Gamer G15</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 895</p>
                    <p><strong>Série: </strong> 231ME027CC9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Kit Teclado</h4>
                    <p><strong>Patrimônio: </strong> 895</p>
                    <p><strong>Série: </strong> 231ME027CC9 - 2241LZX1ULZ8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 896</p>
                    <p><strong>Série: </strong> [Informação não fornecida]</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/helena%20bugliani.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Helena Silva Bugliani</h3>
            <p><strong>Cargo: </strong> Analista de Documentações</p>
            <p><strong>Departamento: </strong> Backoffice</p>
            <p><strong>E-mail: </strong> helena.bugliani@amorsaude.com</p>
            <p><strong>CPF: </strong> 422.795.258-10</p>
            <p><strong>Centro de Custo: </strong> 12005 - Backoffice</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 382</p>
                    <p><strong>Série: </strong> GRFQXW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 566</p>
                    <p><strong>Série: </strong> r9xr500mwvm</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A10s</p>
                    <p><strong>IMEI 1: </strong> 350879696004905/01</p>
                    <p><strong>IMEI 2: </strong> 359753826004906/01</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 372</p>
                    <p><strong>Série: </strong> 2243ce1fb369 - 2240lzx5glw8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 390</p>
                    <p><strong>Série: </strong> 303AZQV96524</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 364</p>
                    <p><strong>Série: </strong> [Informação não fornecida]</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 899</p>
                    <p><strong>Série: </strong> 2332ME021MG9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/helvert%20dias.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Helvert Dias da Silveira Malaquias</h3>
            <p><strong>Cargo: </strong>Analista de Pessoas e Cultura</p>
            <p><strong>Departamento: </strong>Pessoas e Cultura</p>
            <p><strong>E-mail: </strong>helvert.malaquias@amorsaude.com</p>
            <p><strong>CPF: </strong>138.035.246-01</p>
            <p><strong>Centro de Custo: </strong>13006 - Pessoas & Cultura</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>1949</p>
                    <p><strong>Série: </strong>PE0CMYN5</p>
                    <p><strong>Modelo: </strong>Lenovo V15 G3 IAP</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1948</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Dell KB3121WT</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>1146</p>
                    <p><strong>Série: </strong>VN-ORMH97-LXN00-43S-05XD-A01</p>
                    <p><strong>Modelo: </strong>Dell HS2201</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong>1157</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong></p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>;
                    <p><strong>Patrimônio: </strong>1171</p>
                    <p><strong>Série: </strong>PB3B0010CR</p>
                    <p><strong>Modelo: </strong>Moto E14</p>
                    <p><strong>IMEI 1: </strong>354673303308718</p>
                    <p><strong>IMEI 2: </strong>354673303308726</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Henrique Camargo De Lacerda Chaves</h3>
            <p><strong>Cargo:</strong>Consultor(A) De Operações</p>
            <p><strong>Departamento:</strong>Operação</p>
            <p><strong>E-mail:</strong>henrique.chaves@amorsaude.com</p>
            <p><strong>CPF:</strong>375.361.228-61</p>
            <p><strong>Centro de Custo:</strong>12006 - Regional</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio:</strong>07</p>
                    <p><strong>Série:</strong>DMJILH3</p>
                    <p><strong>Modelo:</strong>Dell Ispiron 3501P90F</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio:</strong>646</p>
                    <p><strong>Série:</strong>273748566</p>
                    <p><strong>Modelo:</strong>Jabra HSC0016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>;
                    <p><strong>Patrimônio:</strong>582</p>
                    <p><strong>Série:</strong>R9XT700F6NK</p>
                    <p><strong>Modelo:</strong>Galaxy A03</p>
                    <p><strong>IMEI 1:</strong>354985988580910/01</p>
                    <p><strong>IMEI 2:</strong>355106278590811/01</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio:</strong>252</p>
                    <p><strong>Série:</strong>2232CE1CC278 - 2230LZX1P2Q8</p>
                    <p><strong>Modelo:</strong>Logitech MK235</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/iago%20perin.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Iago Rodrigues Perin</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> iago.perin@amorsaude.com</p>
            <p><strong>CPF: </strong> 424.968.278-14</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 293</p>
                    <p><strong>Série: </strong> 29310378519</p>
                    <p><strong>Modelo: </strong> Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 894</p>
                    <p><strong>Série: </strong> 2316ME027DK9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 435</p>
                    <p><strong>Série: </strong> 2306CE1227A - 2305LZXNXVES</p>
                    <p><strong>Modelo: </strong> Logitech MK235</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 893</p>
                    <p><strong>Série: </strong> [Não fornecido]</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 584</p>
                    <p><strong>Série: </strong> R9XT703J4EV</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 354985989164466/01</p>
                    <p><strong>IMEI 2: </strong> 355106279164467/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/iara%20ribeiro.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Iara Cristina Moraes Ribeiro</h3>
            <p><strong>Cargo: </strong> Analista de Pessoas & Cultura Júnior</p>
            <p><strong>Departamento: </strong> Pessoas & Cultura</p>
            <p><strong>E-mail: </strong> iara.moraes@amorsaude.com</p>
            <p><strong>CPF: </strong> 267.609.978-75</p>
            <p><strong>Data da Admissão: </strong> 04.03.2024</p>
            <p><strong>Centro de Custo: </strong> 13006 - Pessoas & Cultura</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1467</p>
                    <p><strong>Série: </strong> 401AZKA5X845</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 351</p>
                    <p><strong>Série: </strong> DRFQXW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 750</p>
                    <p><strong>Série: </strong> 2125ME0JYP</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 303</p>
                    <p><strong>Série: </strong> 2243CE255CB9, 2241LZX1UF88</p>
                    <p><strong>Modelo: </strong> MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 851</p>
                    <p><strong>Série: </strong> [Não fornecido]</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Igor Hentique de Baggis</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> igor.baggis@amorsaude.com</p>
            <p><strong>CPF: </strong> 429.072.528-79</p>
            <p><strong>Data da Admissão: </strong> [Não fornecida]</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 46</p>
                    <p><strong>Série: </strong> 6F50R23</p>
                    <p><strong>Modelo: </strong> Dell Inspiron 3584</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 768</p>
                    <p><strong>Série: </strong> 2399829109</p>
                    <p><strong>Modelo: </strong> Jabra HSC 015</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 969</p>
                    <p><strong>Série: </strong> [Não fornecida]</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 422</p>
                    <p><strong>Série: </strong> 304AZPU6R704</p>
                    <p><strong>Modelo: </strong> LG 22MP410</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1447</p>
                    <p><strong>Série: </strong> 2229LZXK4618</p>
                    <p><strong>Modelo: </strong> MK270</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/indiara%20miria%20da%20rocha%20siqueira.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Indiara Miria da Rocha Siqueira</h3>
            <p><strong>Cargo: </strong> Assistente de Retenção</p>
            <p><strong>Departamento: </strong> Relacionamento com Cliente</p>
            <p><strong>E-mail: </strong> indiara.siqueira@amorsaude.com</p>
            <p><strong>CPF: </strong> 400.518.438-38</p>
            <p><strong>Data da Admissão: </strong> 07.10.2024</p>
            <p><strong>Centro de Custo: </strong> 72023 - Relacionamento com Cliente / Retenção</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1503</p>
                    <p><strong>Série: </strong> F3Y3F14</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 203</p>
                    <p><strong>Série: </strong> JNZYR0036</p>
                    <p><strong>Modelo: </strong> Logitech K235</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 987</p>
                    <p><strong>Série: </strong> [Não fornecida]</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🎧 Headset</h4>
                    <p><strong>Patrimônio: </strong> 989</p>
                    <p><strong>Série: </strong> 2330AY02D1AB</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/iris%20cristina%20nietto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Iris Cristina Nietto</h3>
            <p><strong>Cargo: </strong> Analista Contábil</p>
            <p><strong>Departamento: </strong> Contabilidade</p>
            <p><strong>E-mail: </strong> iris.nietto@amorsaude.com</p>
            <p><strong>CPF: </strong> 375.074.298-73</p>
            <p><strong>Centro de Custo: </strong> 13003 - Contábil</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1546</p>
                    <p><strong>Série: </strong> PE9014223078</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3 IAP</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1230</p>
                    <p><strong>Série: </strong> [Não fornecida]</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1241</p>
                    <p><strong>Série: </strong> 2350ay0512a8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1545</p>
                    <p><strong>Série: </strong> 2347CE2704D8</p>
                    <p><strong>Modelo: </strong> Logitech Mk270</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1572</p>
                    <p><strong>Série: </strong> 403AZDB84034</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/isabela%20sousa.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Isabela Cristina De Sousa Ponce Santana</h3>
            <p><strong>Cargo: </strong> Consultora De Operações</p>
            <p><strong>Departamento: </strong> Migração</p>
            <p><strong>E-mail: </strong> isabela.sousa@amorsaude.com</p>
            <p><strong>CPF: </strong> 478.374.828-40</p>
            <p><strong>Centro de Custo: </strong></p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1121</p>
                    <p><strong>Série: </strong> 2421AY021KCB</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 219</p>
                    <p><strong>Série: </strong> 83KLOV3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 1012</p>
                    <p><strong>Série: </strong> 2224MECKSA9</p>
                    <p><strong>Modelo: </strong> Galaxy A03 Core</p>
                    <p><strong>IMEI 1: </strong> 3583022353933392/01</p>
                    <p><strong>IMEI 2: </strong> 3583022355393392/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Isabela Hanna Borin</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Operações</p>
            <p><strong>E-mail: </strong> isabela.borin@amorsaude.com</p>
            <p><strong>CPF: </strong> 461.394.038-98</p>
            <p><strong>Centro de Custo: </strong></p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 214</p>
                    <p><strong>Série: </strong> C3klov3</p>
                    <p><strong>Modelo: </strong> Dell/Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 649</p>
                    <p><strong>Série: </strong> 274044668</p>
                    <p><strong>Modelo: </strong> Jabra HSC0016</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> n/e</p>
                    <p><strong>Série: </strong> n/e</p>
                    <p><strong>Modelo: </strong> MO300</p>
                </div>
                <div class="device">
                    <h4>📱 Smartphone</h4>
                    <p><strong>Patrimônio: </strong> 660</p>
                    <p><strong>Série: </strong> r9xta0eecxr</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 350916876987606/01</p>
                    <p><strong>IMEI 2: </strong> 354654236987604/01</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/isabella%20santana.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Isabella Santana</h3>
            <p><strong>Cargo: </strong>Gerente de Operações</p>
            <p><strong>Departamento: </strong>Inteligência de Negócios</p>
            <p><strong>E-mail: </strong>isabella.santana@amorsaude.com</p>
            <p><strong>CPF: </strong></p>
            <p><strong>Centro de Custo: </strong>13021 - Operações</p>
            <img class="status" src="../../media/icon/on.png" alt="">
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Isabella Viera</h3>
            <p><strong>Cargo: </strong> Estagiária</p>
            <p><strong>Departamento: </strong> Pessoas e Cultura</p>
            <p><strong>E-mail: </strong> isabella.vieira@amorsaude.com</p>
            <p><strong>CPF: </strong> 476.994.678-33</p>
            <p><strong>Centro de Custo: </strong> 13006 - Pessoas & Cultura</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 153</p>
                    <p><strong>Série: </strong> DQK4422</p>
                    <p><strong>Modelo: </strong> Dell Inspiron 15</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 732</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 492</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Logitech K270 M185</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 915</p>
                    <p><strong>Série: </strong> 2232ME0208J8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/isabelle%20castilho.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Isabelle Carorile Salvadego Castilho</h3>
            <p><strong>Cargo: </strong> Estagiário Financeiro</p>
            <p><strong>Departamento: </strong> Financeiro</p>
            <p><strong>E-mail: </strong> isabelle.castilho@amorsaude.com</p>
            <p><strong>CPF: </strong> 548.330.698-81</p>
            <p><strong>Centro de Custo: </strong> 13001 - Financeiro</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 928</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 833</p>
                    <p><strong>Série: </strong> 2309ME013GS9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 270</p>
                    <p><strong>Série: </strong> GFQN4T3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/isadora%20santos.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Isadora Solbaro Dos Santos</h3>
            <p><strong>Cargo: </strong> Estagiário(a)</p>
            <p><strong>Departamento: </strong> Backoffice</p>
            <p><strong>E-mail: </strong> isadora.santos@amorsaude.com</p>
            <p><strong>CPF: </strong> 527.864.628-43</p>
            <p><strong>Centro de Custo: </strong> 12005 - BackOffice</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 406</p>
                    <p><strong>Série: </strong> 304AZSP6Y942</p>
                    <p><strong>Modelo: </strong> LG 22MP410</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 249</p>
                    <p><strong>Série: </strong> 6SQXWE</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1021</p>
                    <p><strong>Série: </strong> 2232ME02SZ29</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 471</p>
                    <p><strong>Teclado S/N: </strong> 2232CE1CFB38</p>
                    <p><strong>Mouse S/N: </strong> 2230LZX1RAZ8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Série: </strong> R9XW20CHL6D</p>
                    <p><strong>IMEI 1: </strong> 35830223539624/01</p>
                    <p><strong>IMEI 2: </strong> 35976405393620/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/jade%20rodrigues.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Jade Rodrigues</h3>
            <p><strong>Cargo: </strong> Analista</p>
            <p><strong>Departamento: </strong> Amorcirurgias</p>
            <p><strong>E-mail: </strong> jade.rodrigues@amorsaude.com</p>
            <p><strong>CPF: </strong> 435.810.868-75</p>
            <p><strong>Centro de Custo: </strong> 33009 - Coordenação Clientes</p>
            <p><strong>Promoção: </strong> 01.07.2024</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 268</p>
                    <p><strong>Série: </strong> JN3MOT3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> (Não fornecido)</p>
                    <p><strong>Série: </strong> (Não fornecido)</p>
                    <p><strong>Modelo: </strong> (Não fornecido)</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1549</p>
                    <p><strong>Série: </strong> 2347CE267FE8, 2344LZBQG08</p>
                    <p><strong>Modelo: </strong> Logite MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1031</p>
                    <p><strong>Série: </strong> 2232ME02UL99</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1202</p>
                    <p><strong>Série: </strong> (Não fornecido)</p>
                    <p><strong>Modelo: </strong> Teclado e Mouse</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 1201</p>
                    <p><strong>Série: </strong> zf5242k6hs</p>
                    <p><strong>Modelo: </strong> Moto E22</p>
                    <p><strong>IMEI 1: </strong> 353689478471017/05</p>
                    <p><strong>IMEI 2: </strong> 353689478471025/05</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/janaima%20framcoza.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Janaina Françoza</h3>
            <p><strong>Cargo: </strong> Analista de Documentações Pl</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> janaina.francoza@amorsaude.com</p>
            <p><strong>CPF: </strong> 218.279.938-98</p>
            <p><strong>Centro de Custo: </strong> 12005 - BackOffice</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 140</p>
                    <p><strong>Série: </strong> 8614sn3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15P112F</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 634</p>
                    <p><strong>Série: </strong> R9qt403wmct</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy</p>
                    <p><strong>IMEI 1: </strong> 351989271932203/01</p>
                    <p><strong>IMEI 2: </strong> 353297231932208/01</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 821</p>
                    <p><strong>Série: </strong> (Não fornecido)</p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 260</p>
                    <p><strong>Série: </strong> 2243CE258159 - 2240LZN1KEY8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 837</p>
                    <p><strong>Série: </strong> 2309ME013N89</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/jaqueline%20roviero.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Jaqueline Roviero</h3>
            <p><strong>Cargo: </strong> Analista De Experiência Do Cliente</p>
            <p><strong>Departamento: </strong> Administronline</p>
            <p><strong>E-mail: </strong> jaqueline.roviero@amorsaude.com</p>
            <p><strong>CPF: </strong> 345.888.738-57</p>
            <p><strong>Centro de Custo: </strong> 32001 - Administronline</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 957</p>
                    <p><strong>Série: </strong> (Não fornecido)</p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 480</p>
                    <p><strong>Série: </strong> 2232CE1DO248 - 223OLZX3BLE8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 João Pedro Pereira Ribeiro</h3>
            <p><strong>Cargo: </strong> Desenvolvedor FullStack</p>
            <p><strong>Departamento: </strong> Desenvolvimento</p>
            <p><strong>E-mail: </strong> (não fornecido)</p>
            <p><strong>CPF: </strong> 081.401.955-29</p>
            <p><strong>Centro de Custo: </strong> 13012 - Desenvolvimento</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio:</strong>1174</p>
                    <p><strong>Série:</strong></p>
                    <p><strong>Modelo:</strong>Aluminio</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio:</strong>1173</p>
                    <p><strong>Série:</strong>vn-ormh97-lxn00-43s-050h-a01</p>
                    <p><strong>Modelo:</strong>Dell HS2201</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio:</strong>1526</p>
                    <p><strong>Série:</strong>2345CE4B4F8</p>
                    <p><strong>Modelo:</strong>Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio:</strong>1194</p>
                    <p><strong>Série:</strong>peoc9bxc</p>
                    <p><strong>Modelo:</strong>Lenovo Win-11</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 João Pedro Silvestre</h3>
            <p><strong>Cargo: </strong> Analista de Desempenho</p>
            <p><strong>Departamento: </strong> Inteligência de Negócio</p>
            <p><strong>E-mail: </strong> joaopedro.silvestre@amorsaude.com</p>
            <p><strong>CPF: </strong> N/A</p>
            <p><strong>Centro de Custo: </strong> 12010</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 231</p>
                    <p><strong>Série: </strong> 1KLOV3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 806</p>
                    <p><strong>Série: </strong> (Não fornecido)</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 526</p>
                    <p><strong>Série: </strong> 262807398</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> (Não fornecido)</p>
                    <p><strong>Série: </strong> r9qt402f53k</p>
                    <p><strong>Modelo: </strong> Samsung A03</p>
                    <p><strong>IMEI 1: </strong> 351989271786260/01</p>
                    <p><strong>IMEI 2: </strong> 35329231786265/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/Joao%20Vitor%20Silva%20Alves.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 João Vitor Silva Alves</h3>
            <p><strong>Cargo: </strong>Analista de Suporte</p>
            <p><strong>Departamento: </strong>Suporte</p>
            <p><strong>E-mail: </strong>joao.alves@amorsaude.com</p>
            <p><strong>CPF: </strong>476.331.648-67</p>
            <p><strong>Centro de Custo: </strong>13019 - Suporte</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong></p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong></p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong></p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong></p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong></p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>;
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong></p>
                    <p><strong>IMEI 1: </strong></p>
                    <p><strong>IMEI 2: </strong></p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/Jocasta%20Pires.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Jocasta Pires</h3>
            <p><strong>Cargo:</strong>Analista de Navegação</p>
            <p><strong>Departamento:</strong>Cirurgias</p>
            <p><strong>E-mail:</strong>jocasta.pires@amorsaude.com</p>
            <p><strong>CPF:</strong>449.045.788-05</p>
            <p><strong>Centro de Custo:</strong>32000</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio:</strong>1490</p>
                    <p><strong>Série:</strong>PE0D9L3A</p>
                    <p><strong>Modelo:</strong>Dell V15 G3 IAP</p>
                </div>
                <div class="device">
                    <h4>⌨️Teclado</h4>
                    <p><strong>Patrimônio:</strong>1495</p>
                    <p><strong>Série:</strong>08JV2Y-LO300-43CK2DR-A07DPN 08JV2Y</p>
                    <p><strong>Modelo:</strong>Dell</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio:</strong>1061</p>
                    <p><strong>Série:</strong>08JV2Y-LO300-43CM2DR-A07DPN 0JV2Y</p>
                    <p><strong>Modelo:</strong>Dell</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio:</strong>966</p>
                    <p><strong>Série:</strong>2329AY0862</p>
                    <p><strong>Modelo:</strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio:</strong>1049</p>
                    <p><strong>Série:</strong></p>
                    <p><strong>Modelo:</strong>Aluminio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Jonatas Henrique Esreves Mian</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong></p>
            <p><strong>CPF: </strong> 361.772.308-06</p>
            <p><strong>Centro de Custo: </strong></p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 165</p>
                    <p><strong>Série: </strong> PE09784E</p>
                    <p><strong>Modelo: </strong> Ideapad3 15alc6</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 794</p>
                    <p><strong>Série: </strong> RX8R90HDAAR</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 354397310709045/01</p>
                    <p><strong>IMEI 2: </strong> 354578130709048/01</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 70</p>
                    <p><strong>Série: </strong> 6885d83</p>
                    <p><strong>Modelo: </strong> Dell E1920HF</p>
                </div>
                <div class="device">
                    <h4>🔌 Adaptador de Rede</h4>
                    <p><strong>Patrimônio: </strong> 553</p>
                    <p><strong>Série: </strong> dbd075pf</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1010</p>
                    <p><strong>Série: </strong> 881-000466</p>
                    <p><strong>Modelo: </strong> Logitech MO300</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Jordy Neves</h3>
            <p><strong>Cargo: </strong> Analista De Desempenho</p>
            <p><strong>Departamento: </strong> Inteligência de Negócio</p>
            <p><strong>E-mail: </strong> jordy.neves@amorsaude.com</p>
            <p><strong>CPF: </strong> N/A</p>
            <p><strong>Centro de Custo: </strong> 12010</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 223</p>
                    <p><strong>Série: </strong> 4JWXXT3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 400</p>
                    <p><strong>Série: </strong> 303AZQ96500</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Jorge Augusto Zaidan</h3>
            <p><strong>Cargo: </strong>Coordenador de BackOffice</p>
            <p><strong>Departamento: </strong>BackOffice</p>
            <p><strong>E-mail: </strong>jorge.zaidan@amorsaude.com</p>
            <p><strong>CPF: </strong>082.522.536-10</p>
            <p><strong>Centro de Custo: </strong>12017 - Gerência Operações</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong></p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong>1999</p>
                    <p><strong>Série: </strong>CN-08JY2Y-LO300-48U-K4FF-A07</p>
                    <p><strong>Modelo: </strong>Dell KM5221W</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>1055</p>
                    <p><strong>Série: </strong>2433AYE01328</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong>1066</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Aluminio</p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> Coletar Informação</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 José Vinicius Menari</h3>
            <p><strong>Cargo:</strong>Desenvolvedor Sênior</p>
            <p><strong>Departamento:</strong>Desenvolvimento</p>
            <p><strong>E-mail:</strong>jose.menari@amorsaude.com</p>
            <p><strong>CPF:</strong>462.285.768-52</p>
            <p><strong>Centro de Custo:</strong>13012 - Desenvolvimento</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio:</strong></p>
                    <p><strong>Série:</strong>NHQEYAL0053290360F9Z00</p>
                    <p><strong>Modelo:</strong>Nitro AN515-45 AMD Ryzen 7 5800H 16GB</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio:</strong>474</p>
                    <p><strong>Série:</strong>2232CE1CFB08 - 2229LZXMWT68</p>
                    <p><strong>Modelo:</strong>Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio:</strong>943</p>
                    <p><strong>Série:</strong>2330AYO42778</p>
                    <p><strong>Modelo:</strong>LogiTech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio:</strong>942</p>
                    <p><strong>Série:</strong></p>
                    <p><strong>Modelo:</strong>Aluminio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>

        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/josiane%20santos.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Josiane Santos</h3>
            <p><strong>Cargo: </strong> Analista De QA</p>
            <p><strong>Departamento: </strong> Produto</p>
            <p><strong>E-mail: </strong> josiane.santos@amorsaude.com</p>
            <p><strong>CPF: </strong> N/A</p>
            <p><strong>Centro de Custo: </strong> 13013 - Produto</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <!-- Nenhum dispositivo foi fornecido ainda -->
            </div>
            <!-- Nenhuma informação de Equipamentos Comprometidos -->
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/julcelly%20henriques.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Julcelly Henriques</h3>
            <p><strong>Cargo: </strong> Analista De Pesquisa Jr</p>
            <p><strong>Departamento: </strong> Projetos</p>
            <p><strong>E-mail: </strong> julcelly.henriques@amorsaude.com</p>
            <p><strong>CPF: </strong> N/A</p>
            <p><strong>Centro de Custo: </strong> 13004</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 404</p>
                    <p><strong>Série: </strong> 302AZHY7F913</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/julia%20colombari.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Julia Colombari</h3>
            <p><strong>Cargo: </strong> Assistente de CS</p>
            <p><strong>Departamento: </strong> Atendimento ao Cliente</p>
            <p><strong>E-mail: </strong> julia.colombari@amorsaude.com</p>
            <p><strong>CPF: </strong> 497.615.898-54</p>
            <p><strong>Centro de Custo: </strong> 12002 - SAC</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 213</p>
                    <p><strong>Série: </strong> 42KLov3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 648</p>
                    <p><strong>Série: </strong> 273938593</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 659</p>
                    <p><strong>Série: </strong> r9xta0efy0t</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 350916877025638/01</p>
                    <p><strong>IMEI 2: </strong> 354654237025636/01</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 341 - 342</p>
                    <p><strong>Série Teclado: </strong> 2234CE182269</p>
                    <p><strong>Série Mouse: </strong> 2230LZN10ED8</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 733</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/julia%20silva.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Julia Carolia da Silva</h3>
            <p><strong>Cargo: </strong> Assistente de Atendimento ao Cliente</p>
            <p><strong>Departamento: </strong> Atendimento ao Cliente</p>
            <p><strong>E-mail: </strong> julia.silva@amorsaude.com</p>
            <p><strong>CPF: </strong> 439.325.638-75</p>
            <p><strong>Centro de Custo: </strong> 12002 - Sac</p>
            <p><strong>Substituição: </strong> Dariane da Silva Romerio</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 637</p>
                    <p><strong>Série: </strong> 273961635</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 345</p>
                    <p><strong>Série: </strong> 2234CE182228</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 331</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🔌 Carregador</h4>
                    <p><strong>Patrimônio: </strong> 1215</p>
                    <p><strong>Série: </strong> R37T8WZ3803SCA</p>
                    <p><strong>Modelo: </strong> EP-TA50BW</p>
                </div>
                <div class="device">
                    <h4>⚡ Fonte</h4>
                    <p><strong>Patrimônio: </strong> 1013</p>
                    <p><strong>Série: </strong> 8SSA10L1BR29E002C</p>
                    <p><strong>Modelo: </strong> ADLX65CLGB2A</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 208</p>
                    <p><strong>Série: </strong> PE09XVKX</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G2 ITL</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 640</p>
                    <p><strong>Série: </strong> R9XT9089MAW</p>
                    <p><strong>Modelo: </strong> Galaxy A03s</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/juliana%20de%20oliveira%20silverio.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Juliana de Oliveira Silvério</h3>
            <p><strong>Cargo: </strong> Consultor de Operação</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> juliana.silverio@amorsaude.com</p>
            <p><strong>CPF: </strong> 293.701.128-47</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <p><strong>Data de Admissão: </strong> 06.06.2022</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 459</p>
                    <p><strong>Série: </strong> 2232CE1CFB68 - 2230LZX1R9F8</p>
                    <p><strong>Modelo: </strong> K270 - M185</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 923</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 294</p>
                    <p><strong>Série: </strong> HN3MOT3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 351</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1124</p>
                    <p><strong>Série: </strong> 2421AY01PH38</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 924</p>
                    <p><strong>Série: </strong> 2332ME01XL08</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/juliana%20souza.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Juliana Paula da Silva Souza</h3>
            <p><strong>Cargo: </strong> Analista de Pessoas e Cultura</p>
            <p><strong>Departamento: </strong> Pessoas & Cultura</p>
            <p><strong>E-mail: </strong> juliana.souza@amorsaude.com</p>
            <p><strong>CPF: </strong> 407.054.928-50</p>
            <p><strong>Centro de Custo: </strong> 13006 - Pessoas & Cultura</p>
            <p><strong>Data de Desligamento: </strong> 19.01.2024</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 005</p>
                    <p><strong>Série: </strong> 1tn0513</p>
                    <p><strong>Modelo: </strong> Dell Inspiron 3501 P90F</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 735</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 716</p>
                    <p><strong>Série: </strong> R9XT202F3JW</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 35498580107183/01</p>
                    <p><strong>IMEI 2: </strong> 355106270107184/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/karen%20ramos.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Karen Ramos</h3>
            <p><strong>Cargo: </strong> Analista De Treinamentos</p>
            <p><strong>Departamento: </strong> BackOffice</p>
            <p><strong>E-mail: </strong> karen.ramos@amorsaude.com</p>
            <p><strong>CPF: </strong> 405.22.938-08</p>
            <p><strong>Centro de Custo: </strong> 13020 - Treinamentos</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 396</p>
                    <p><strong>Série: </strong> 303AZWS94377</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> O colaborador está utilizando os equipamentos listados, porém apenas possui
                    o termo de responsabilidade assinado para o monitor, não tendo os dados e termo para os demais
                    itens.</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/karen%20loureiro.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Karen Loureiro</h3>
            <p><strong>Cargo: </strong> Consultora de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>CPF: </strong> 387.565.348-33</p>
            <p><strong>Centro de Custo: </strong>12006 - Regional 1</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 322</p>
                    <p><strong>Série: </strong> 80G8YX3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 879</p>
                    <p><strong>Série: </strong> 2316ME027FK9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 826</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 325</p>
                    <p><strong>Série: </strong> CN-ONCRCC-LO300-311-KOUH-A01</p>
                    <p><strong>Modelo: </strong> Dell KM5221W</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Katia Tatiana Tomaz Marcondes</h3>
            <p><strong>Cargo: </strong> Assistente de Atendimento ao Cliente</p>
            <p><strong>Departamento: </strong> Atendimento ao Cliente</p>
            <p><strong>E-mail: </strong>katia.marcondes@amorsaude.com</p>
            <p><strong>CPF: </strong> 317.842.138-61</p>
            <p><strong>Centro de Custo: </strong> 12002 - Sac</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Headset</h4>
                    <p><strong>Patrimônio: </strong> 1020</p>
                    <p><strong>Série: </strong> 23331AY020B49</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1019</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1529</p>
                    <p><strong>Série: </strong> 2345CE4BA518</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 1228</p>
                    <p><strong>Série: </strong> ZF5244C2KF</p>
                    <p><strong>Modelo: </strong> Moto E22</p>
                    <p><strong>IMEI 1: </strong> 352800824226754</p>
                    <p><strong>IMEI 2: </strong> 352800824226762</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1477</p>
                    <p><strong>Série: </strong> 403AZBZ6J261</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook Win-11 i5-1235U 8GB</h4>
                    <p><strong>Patrimônio: </strong> 1528</p>
                    <p><strong>Série: </strong> PEOC4E5</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/kessya%20rosolini.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Kessya De Lima Rosolini</h3>
            <p><strong>Cargo: </strong> Coordenador(A) De Pessoas E Cultura</p>
            <p><strong>Departamento: </strong> Pessoas E Cultura</p>
            <p><strong>CPF: </strong> 433.684.318-03</p>
            <p><strong>Centro de Custo: </strong> 13006</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 405</p>
                    <p><strong>Série: </strong> JRFQXW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong> 595</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 1028</p>
                    <p><strong>Série: </strong> R9XW20CHL5J</p>
                    <p><strong>Modelo: </strong> Galaxy A03 Core</p>
                    <p><strong>IMEI 1: </strong> 358302235393616/01</p>
                    <p><strong>IMEI 2: </strong> 3597964053393612/01</p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> O colaborador está utilizando os equipamentos listados, porém não possuímos
                    o termo de responsabilidade do teclado e mouse.</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/lais%20toledo.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Lais Toledo Mendes da Silva</h3>
            <p><strong>Cargo: </strong> Assistente Contábil</p>
            <p><strong>Departamento: </strong> Contabilidade</p>
            <p><strong>E-mail: </strong>kessya.rosolini@amorsaude.com</p>
            <p><strong>CPF: </strong> 339.643.328-95</p>
            <p><strong>Centro de Custo: </strong> 13003 - Contábil</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1242</p>
                    <p><strong>Série: </strong> 2316GG00ASR8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1543</p>
                    <p><strong>Série: </strong> 2347CE268008</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1544</p>
                    <p><strong>Série: </strong> PE0C4SLQ</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3IAP</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1243</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1571</p>
                    <p><strong>Série: </strong> 402AZTH8V665</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/larissa%20branco%20ribeiro.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Larissa Branco Ribeiro</h3>
            <p><strong>Cargo: </strong> Assistente de Operações</p>
            <p><strong>Departamento: </strong> BackOffice</p>
            <p><strong>CPF: </strong> 403.473.789-00</p>
            <p><strong>Centro de Custo: </strong> 12005 - BackOffice</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 429</p>
                    <p><strong>Série: </strong> CN-0NCRCC-LO300-331-KOUV-A01</p>
                    <p><strong>Modelo: </strong> Dell KM 5221</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 191</p>
                    <p><strong>Série: </strong> pe09ftl5</p>
                    <p><strong>Modelo: </strong> Ideapad3 15alc6</p>
                </div>
                <div class="device"> Onde esta?
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 241</p>
                    <p><strong>Série: </strong> 303AZUJ95214</p>
                    <p><strong>Modelo: </strong> LG 22MP410</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 494</p>
                    <p><strong>Série: </strong> 308AZHY0E545</p>
                    <p><strong>Modelo: </strong> LG 22MP410</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 815</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🌐 Adaptador de Rede</h4>
                    <p><strong>Patrimônio: </strong> 613</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> N/A</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 615</p>
                    <p><strong>Série: </strong> 265438192</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1236</p>
                    <p><strong>Série: </strong> 2316GG00ASS8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/larissa%20santos.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Larissa Gabrielle Rosseto Dos Santos</h3>
            <p><strong>Cargo: </strong> Estagiário(a)</p>
            <p><strong>Departamento: </strong> Departamento Pessoal</p>
            <p><strong>E-mail: </strong>larissa.santos@amorsaude.com</p>
            <p><strong>CPF: </strong> 485.231.268-05</p>
            <p><strong>Centro de Custo: </strong> 13006 - Pessoa e Cultura</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 351</p>
                    <p><strong>Série: </strong> DRFQXW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 153510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 750</p>
                    <p><strong>Série: </strong> 2125ME0JYP</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 694</p>
                    <p><strong>Série: </strong> R9XTA0EGANI</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 350916877029812/01</p>
                    <p><strong>IMEI 2: </strong> 354554237029810/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/larissa%20izabela%20de%20freitas.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Larissa Izabela de Freitas</h3>
            <p><strong>Cargo: </strong> Analista Administronline Pleno</p>
            <p><strong>Departamento: </strong> Cirurgias</p>
            <p><strong>CPF: </strong> 470.350.438-37</p>
            <p><strong>Centro de Custo: </strong> 32001 - Cirurgias</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 970</p>
                    <p><strong>Série: </strong> 2330AY032488</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1449</p>
                    <p><strong>Série: </strong> 4LBFD14</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 930</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1448</p>
                    <p><strong>Série: </strong> 2334CE2DA878 - 2331LXRRGD8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1465</p>
                    <p><strong>Série: </strong> 401AZQV62940</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 1217</p>
                    <p><strong>Série: </strong> ZF52489JLP</p>
                    <p><strong>IMEI 1: </strong> 359340983517837/15</p>
                    <p><strong>IMEI 2: </strong> 359340983517845/15</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/larissa%20mattozo.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Larissa Magiolo Mattozo</h3>
            <p><strong>Cargo: </strong> Product Owner Jr</p>
            <p><strong>Departamento: </strong> Produto</p>
            <p><strong>CPF: </strong> 386.596.578-45</p>
            <p><strong>Centro de Custo: </strong> 13013 - Produto</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 207</p>
                    <p><strong>Série: </strong> PE09XVKW</p>
                    <p><strong>Modelo: </strong> Lenovo V15 82ME</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 638</p>
                    <p><strong>Série: </strong> 273749505</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 641</p>
                    <p><strong>Série: </strong> R9XT9088SPH</p>
                    <p><strong>IMEI 1: </strong> 353541243429830/01</p>
                    <p><strong>IMEI 2: </strong> 354183763429830/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/laura%20ribeiro.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Laura Barros Ribeiro</h3>
            <p><strong>Cargo: </strong> Analista De Pessoas E Cultura</p>
            <p><strong>Departamento: </strong> Pessoas & Cultura</p>
            <p><strong>E-mail: </strong> laura.ribeiro@amorsaude.com</p>
            <p><strong>CPF: </strong></p>
            <p><strong>Centro de Custo: </strong> 13006 - Pessoas & Cultura</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> O colaborador está utilizando equipamentos sem registro detalhado e sem o
                    termo de responsabilidade assinado.</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/laura%20paschoin.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Laura Garcia Paschoin</h3>
            <p><strong>Cargo: </strong> Analista de Projetos</p>
            <p><strong>Departamento: </strong> Projetos Processos</p>
            <p><strong>E-mail: </strong> laura.paschoin@amorsaude.com</p>
            <p><strong>CPF: </strong> 432.486.208-70</p>
            <p><strong>Centro de Custo: </strong> 13004 - Projetos / Processos</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 190</p>
                    <p><strong>Série: </strong> pe09ftjn</p>
                    <p><strong>Modelo: </strong> Lenovo Ideapad3 15alc6</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 237</p>
                    <p><strong>Série: </strong> 2253CE0F1789</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                    <p><strong>Mouse Série: </strong> 2248LZX6ND08</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 803</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 397</p>
                    <p><strong>Série: </strong> 303AZPU94384</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 601</p>
                    <p><strong>Série: </strong> 265441361</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 591</p>
                    <p><strong>Série: </strong> R9XT700F4VR</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 354985988590316/01</p>
                    <p><strong>IMEI 2: </strong> 355106278580317/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/lauriele%20santos.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Lauriele Santiago dos Santos</h3>
            <p><strong>Cargo: </strong> Assistente Financeiro</p>
            <p><strong>Departamento: </strong> Analista de Financeiro</p>
            <p><strong>E-mail: </strong> laurielesantos@amorsaude.com</p>
            <p><strong>CPF: </strong> 457.240.988-90</p>
            <p><strong>Centro de Custo: </strong> 13001 - Financeiro</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 2016</p>
                    <p><strong>Série (Teclado): </strong> 229CE14CDA8</p>
                    <p><strong>Série (Mouse): </strong> 2237LZN0LDT8</p>
                    <p><strong>Modelo: </strong> MK270</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 206</p>
                    <p><strong>Série: </strong> PE09EEK5</p>
                    <p><strong>Modelo: </strong> Lenovo v15 82ME</p>
                </div>
                <div class="device">
                    <h4>🎧 Headset</h4>
                    <p><strong>Patrimônio: </strong> 632</p>
                    <p><strong>Série: </strong> 00254425932</p>
                    <p><strong>Modelo: </strong> jabra HSC0016</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1163</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 721</p>
                    <p><strong>Série: </strong> R9XR500MWLZ</p>
                    <p><strong>Modelo: </strong> Galaxy A10s</p>
                    <p><strong>IMEI 1: </strong> 350879696004822</p>
                    <p><strong>IMEI 2: </strong> 359753826004823</p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> O colaborador está utilizando os equipamentos listados, porém ainda não
                    possui o termo de responsabilidade assinado para os mesmos.</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/leandro%20hernandes.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Leandro Alves Hernandes</h3>
            <p><strong>Cargo: </strong> Gerente Regional De Operações</p>
            <p><strong>Departamento: </strong> Dir. Operações</p>
            <p><strong>E-mail: </strong> leandro.hernandes@amorsaude.com</p>
            <p><strong>CPF: </strong> 251.829.328-04</p>
            <p><strong>Centro de Custo: </strong> 12000 - Dir. Operações</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 259</p>
                    <p><strong>Série (Teclado): </strong> 2243CE258169</p>
                    <p><strong>Série (Mouse): </strong> 2240LZN1KFW8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 216</p>
                    <p><strong>Série: </strong> 53KLOV3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 651</p>
                    <p><strong>Série: </strong> 273964312</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 658</p>
                    <p><strong>Série: </strong> R9xta03gbwf</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 350916877030216/01</p>
                    <p><strong>IMEI 2: </strong> 354654237030214/01</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 822</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <!-- Se houver equipamentos comprometidos, esta seção será gerada. Caso contrário, será omitida. -->
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/leandro%20rodrigues.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Leandro Rodrigues Santos</h3>
            <p><strong>Cargo: </strong> Assistente de Treinamentos</p>
            <p><strong>Departamento: </strong> BackOffice</p>
            <p><strong>E-mail: </strong> leandro.rodrigues@amorsaude.com</p>
            <p><strong>CPF: </strong> 381.953.408-30</p>
            <p><strong>Centro de Custo: </strong> 12005 - Backoffice</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1946</p>
                    <p><strong>Série (Teclado): </strong> 2243CE258149</p>
                    <p><strong>Série (Mouse): </strong> 2344LZXBR628</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 165</p>
                    <p><strong>Série: </strong> PE09784e</p>
                    <p><strong>Modelo: </strong> Ideapad315alc6</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 741</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 1054</p>
                    <p><strong>Série: </strong> ZF524CGM4K</p>
                    <p><strong>Modelo: </strong> Motorola E22</p>
                    <p><strong>IMEI 1: </strong> 352500458552414</p>
                    <p><strong>IMEI 2: </strong> 352500458552422</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1168</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <!-- Se houver equipamentos comprometidos, esta seção será gerada. Caso contrário, será omitida. -->
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> O colaborador está utilizando os equipamentos listados, porém somente
                    possuímos o termo de responsabilidade do celular.</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/leandro%20montenegro.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Leandro Tosta Montenegro</h3>
            <p><strong>Cargo: </strong> Analista de Pessoas & Cultura Júnior</p>
            <p><strong>Departamento: </strong> Pessoas & Cultura</p>
            <p><strong>E-mail: </strong> leandro.montenegro@amorsaude.com</p>
            <p><strong>CPF: </strong> 385.498.628-94</p>
            <p><strong>Centro de Custo: </strong> 13006 - Pessoas & Cultura</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 250</p>
                    <p><strong>Série: </strong> 1SFQXW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1023</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 304</p>
                    <p><strong>Série: </strong> 2243CE255CD8</p>
                    <p><strong>Modelo: </strong> MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 850</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 307</p>
                    <p><strong>Série: </strong> 304AZZX6R709</p>
                    <p><strong>Modelo: </strong> LG MP410</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 1024</p>
                    <p><strong>Série: </strong> R9XW20CHJVB</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 358302235393186/01</p>
                    <p><strong>IMEI 2: </strong> 359796405393182/01</p>
                </div>
            </div>
            <!-- Não foi gerada a seção de Informações Importantes, pois não há alerta fornecido para este colaborador -->
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/leonardo%20botelho.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Leonardo Cotrim Ferreira Pinto de Oliveira Botelho</h3>
            <p><strong>Cargo: </strong> Gerente Técnico</p>
            <p><strong>Departamento: </strong> Gerência Técnica</p>
            <p><strong>E-mail: </strong> leonardo.botelho@amorsaude.com</p>
            <p><strong>CPF: </strong> 129.654.087-11</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1437</p>
                    <p><strong>Série: </strong> 9C32JZE</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 811</p>
                    <p><strong>Série: </strong> 2235ME00Y2R9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 445</p>
                    <p><strong>Série: </strong> 2320CE1626A8 - 2316LZX3G3P8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1466</p>
                    <p><strong>Série: </strong> 401AZKA1B021</p>
                    <p><strong>Modelo: </strong> LG-22MP410-B</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 961</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 586</p>
                    <p><strong>Série: </strong> r9xt703h7td</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 354985989154699/01</p>
                    <p><strong>IMEI 2: </strong> 355106279754690/01</p>
                </div>
            </div>
            <!-- A seção de Informações Importantes foi gerada com o alerta sobre equipamentos não listados -->
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> O colaborador está utilizando outros equipamentos não listados acima, porém
                    somente possui o termo de responsabilidade assinado para o celular.</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Leonardo Tavares de Mello</h3>
            <p><strong>Cargo: </strong>Product Owner Jr</p>
            <p><strong>Departamento: </strong>Produto</p>
            <p><strong>E-mail: </strong>leonardo.mello@amorsaude.com</p>
            <p><strong>CPF: </strong>402.458.348-42</p>
            <p><strong>Centro de Custo: </strong>13013 - Produto</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>1957</p>
                    <p><strong>Série: </strong>PE0CMYL3</p>
                    <p><strong>Modelo: </strong>Lenovo G3 IAP</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Teclado Patrimônio: </strong>1953</p>
                    <p><strong>Mouse Patrimônio: </strong>856</p>
                    <p><strong>Série: </strong>cn-oncrcc-lo300-34c-k1gz-a01</p>
                    <p><strong>Modelo: </strong>Dell KB3121WT</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>1247</p>
                    <p><strong>Série: </strong>VN-0rmh97-lxn00-43s-05tn-a01</p>
                    <p><strong>Modelo: </strong>Dell HS2201</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong></p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> Verificar Monitor e Coletar Termo</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/lethicia%20eliane%20souza%20martins.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Lethicia Eliane Souza Martins</h3>
            <p><strong>Cargo: </strong> Assistente Administronline</p>
            <p><strong>Departamento: </strong> BackOffice</p>
            <p><strong>E-mail: </strong> lethicia.martins@amorsaude.com</p>
            <p><strong>CPF: </strong> 480.185.558-00</p>
            <p><strong>Centro de Custo: </strong> 12005 - Backoffice</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 433</p>
                    <p><strong>Série: </strong> 224CE257B58</p>
                    <p><strong>Modelo: </strong> MK270</p>
                </div>
                <div class="device">
                    <h4>🖱️Mouse</h4>
                    <p><strong>Patrimônio: </strong> 433</p>
                    <p><strong>Série: </strong> 2241LZX1ULS8</p>
                    <p><strong>Modelo: </strong> M185</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 248</p>
                    <p><strong>Série: </strong> 9JLQXW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Headset</h4>
                    <p><strong>Patrimônio: </strong> 1019</p>
                    <p><strong>Série: </strong> 2224MECKSFU9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 391</p>
                    <p><strong>Série: </strong> 303AZSP96526</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/leticia%20silva.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Letícia Godoy da Silva</h3>
            <p><strong>Cargo: </strong> Analista de Pessoas & Cultura Júnior</p>
            <p><strong>Departamento: </strong> Pessoas & Cultura</p>
            <p><strong>E-mail: </strong> leticia.silva@amorsaude.com</p>
            <p><strong>CPF: </strong> 439.857.398-46</p>
            <p><strong>Centro de Custo: </strong> 13006 - Pessoas & Cultura</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 230</p>
                    <p><strong>Série: </strong> 54KL0V3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Headset</h4>
                    <p><strong>Patrimônio: </strong> 678</p>
                    <p><strong>Série: </strong> 274045806</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Patrimônio: </strong> (Não fornecido)</p>
                    <p><strong>Modelo: </strong> Multilaser MO300</p>
                </div>
                <div class="device">
                    <h4>📱 Smartphone</h4>
                    <p><strong>Patrimônio: </strong> 674</p>
                    <p><strong>Série: </strong> r9xta0etcqk</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 350916876991889/01</p>
                    <p><strong>IMEI 2: </strong> 354654236991887/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/leticia%20osone.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Leticia Yumi Osone</h3>
            <p><strong>Cargo: </strong> Analista De Experiência Do Cliente</p>
            <p><strong>Departamento: </strong> Qualidade Processo</p>
            <p><strong>E-mail: </strong> leticia.osone@amorsaude.com</p>
            <p><strong>CPF: </strong> 362.189.578-70</p>
            <p><strong>Centro de Custo: </strong> 12004 - Experiência do Cliente</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 485</p>
                    <p><strong>Série: </strong> 308AZWS8S585</p>
                    <p><strong>Modelo: </strong> LG22MP410-B</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 211</p>
                    <p><strong>Série: </strong> pe09xvkt</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G2</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 868</p>
                    <p><strong>Série: </strong> 2315AY078409</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 874</p>
                    <p><strong>Série: </strong> (Não fornecido)</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 421</p>
                    <p><strong>Série: </strong> cn-oncrcc-lo300-331-k14w=a01</p>
                    <p><strong>Modelo: </strong> Logitech MK235</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/livia%20paula.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Livia Marcolino Paula</h3>
            <p><strong>Cargo: </strong> Analista de Atendimento ao Cliente JR</p>
            <p><strong>Departamento: </strong> Amorcirurgias</p>
            <p><strong>E-mail: </strong> livia.paula@amorsaude.com</p>
            <p><strong>CPF: </strong> 438.725.158-10</p>
            <p><strong>Centro de Custo: </strong> 32001 - Administronline</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 218</p>
                    <p><strong>Série: </strong> 43KLOV3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 971</p>
                    <p><strong>Série: </strong> 2330A4042098</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 375</p>
                    <p><strong>Série: </strong> 2240CE0B4129</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 367</p>
                    <p><strong>Série: </strong> (Não fornecido)</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 48</p>
                    <p><strong>Série: </strong> BR-0H5MVNV-TVB00-19K1KDI-A12</p>
                    <p><strong>Modelo: </strong> Dell SE2216H</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/lorraine%20moreno.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Lorraine Moreno Machado</h3>
            <p><strong>Cargo: </strong> Assistente de Canais Digitais e Relacionamentos</p>
            <p><strong>Departamento: </strong> Telemedicina</p>
            <p><strong>E-mail: </strong> lorraine.moreno@amorsaude.com</p>
            <p><strong>CPF: </strong> 373.515.648-76</p>
            <p><strong>Centro de Custo: </strong> 22001 - Consulta Assistida</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 449</p>
                    <p><strong>Série: </strong> 4J32JZ3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 456</p>
                    <p><strong>Série: </strong> 2230LZX1R868</p>
                    <p><strong>Modelo: </strong> Logitech K270 - M185</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 954</p>
                    <p><strong>Série: </strong> 2323GGO15J98</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 242</p>
                    <p><strong>Série: </strong> 303AZJT96660</p>
                    <p><strong>Modelo: </strong> 22mpp410-b</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 935</p>
                    <p><strong>Série: </strong> (Não fornecido)</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/luana%20maciel.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Luana Tauani Maciel Alves</h3>
            <p><strong>Cargo: </strong> Analista - Financeiro</p>
            <p><strong>Departamento: </strong> Financeiro</p>
            <p><strong>E-mail: </strong> luana.maciel@amorsaude.com</p>
            <p><strong>CPF: </strong> 319.532.598-55</p>
            <p><strong>Centro de Custo: </strong> 13001 - Financeiro</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 001</p>
                    <p><strong>Série: </strong> h8067z2</p>
                    <p><strong>Modelo: </strong> Dell Inspiron15p75f</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 799</p>
                    <p><strong>Série: </strong> 00027313725020917</p>
                    <p><strong>Modelo: </strong> Jabra HSC015</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 018</p>
                    <p><strong>Série: </strong> 10fh2w2</p>
                    <p><strong>Modelo: </strong> Dell P2018hc</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 793</p>
                    <p><strong>Série: </strong> R9XR500Q4AF</p>
                    <p><strong>Modelo: </strong> Samsung A10S</p>
                    <p><strong>IMEI 1: </strong>350879696029142/01</p>
                    <p><strong>IMEI 2: </strong>35975382609143/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/lucas%20colbacho.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Lucas Colbacho</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> lucas.colbacho@amorsaude.com</p>
            <p><strong>CPF: </strong> 369.193.928-79</p>
            <p><strong>Centro de Custo: </strong> 12006 - Regional 1</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 912</p>
                    <p><strong>Série: </strong> 2332ME01XK68</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1472</p>
                    <p><strong>Série: </strong> 402AZDB4W274</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 956</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 295</p>
                    <p><strong>Série: </strong> 1P3M0T3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 999</p>
                    <p><strong>Série: </strong> ZF52494NGK</p>
                    <p><strong>Modelo: </strong> Moto E13</p>
                    <p><strong>IMEI 1: </strong> 359340983552453/05</p>
                    <p><strong>IMEI 2: </strong> 359340983552461/05</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 297</p>
                    <p><strong>Série: </strong> 2243CE258119 - 2240LZN1KFY8</p>
                    <p><strong>Modelo: </strong> MK270 - M185</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Lucas Guidetti Rocha</h3>
            <p><strong>Cargo: </strong> Assistente de Canais Digitais e Relacionamento</p>
            <p><strong>Departamento: </strong> Telemedicina</p>
            <p><strong>E-mail: </strong> lucas.rocha@amorsaude.com</p>
            <p><strong>CPF: </strong> 460.375.988-60</p>
            <p><strong>Centro de Custo: </strong> 22001 - Consulta Assistida</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 449</p>
                    <p><strong>Série: </strong> 4J32JZ3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 456</p>
                    <p><strong>Série: </strong> 2230LZX1R868</p>
                    <p><strong>Modelo: </strong> Logitech K270 - M185</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 954</p>
                    <p><strong>Série: </strong> 2323GGO15J98</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 242</p>
                    <p><strong>Série: </strong> 303AZJT96660</p>
                    <p><strong>Modelo: </strong> 22mpp410-b</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 935</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/lucas%20mateus.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Lucas Mateus</h3>
            <p><strong>Cargo: </strong> Analista De Dados</p>
            <p><strong>Departamento: </strong> Dados</p>
            <p><strong>E-mail: </strong> lucas.mateus@amorsaude.com</p>
            <p><strong>CPF: </strong> 432.126.858-32</p>
            <p><strong>Centro de Custo: </strong> 13011 - Dados</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1572</p>
                    <p><strong>Série: </strong> 403AZDB84034 </p>
                    <p><strong>Modelo: </strong> LG 22MP10-B </p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor(Criar Termo de Devolução)</h4>
                    <p><strong>Patrimônio: </strong> 1438</p>
                    <p><strong>Série: </strong> F24T350FH</p>
                    <p><strong>Modelo: </strong> Samsung LF24T350FHLMZD</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 63</p>
                    <p><strong>Série: </strong> 5cvgxm3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3510 P112F</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 386</p>
                    <p><strong>Série: </strong> 2235me00y6v9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 814</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 236</p>
                    <p><strong>Série: </strong> 2253CEOF16F9 - 2248LZX6NDX8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Lucas Nicolau Saconato</h3>
            <p><strong>Cargo: </strong>Agile Coach</p>
            <p><strong>Departamento: </strong>Desenvolvimento</p>
            <p><strong>E-mail: </strong>lucas.saconato@amorsaude.com</p>
            <p><strong>CPF: </strong>310.949.798-05</p>
            <p><strong>Centro de Custo: </strong>13012 - Desenvolvimento</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> Coletar dados dos equipamentos e coletar termo</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/lucas%20pedro%20pereira.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Lucas Pedro Pereira</h3>
            <p><strong>Cargo: </strong> Analista Financeiro JR</p>
            <p><strong>Departamento: </strong> Financeiro</p>
            <p><strong>E-mail: </strong> lucas.pereira@amorsaude.com</p>
            <p><strong>CPF: </strong> 461.067.698-26</p>
            <p><strong>Centro de Custo: </strong> 63001 - Financeiro Soluções</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Headset</h4>
                    <p><strong>Patrimônio: </strong> 1016</p>
                    <p><strong>Série: </strong> 2350AY052028</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1017</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> N/A</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> N/A</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1527</p>
                    <p><strong>Série: </strong> PE0CB4VF</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3 (Win-11 i5-1235U 8GB)</p>
                </div>
                <div class="device">
                    <h4>🔌 Fonte</h4>
                    <p><strong>Patrimônio: </strong> 1018</p>
                    <p><strong>Série: </strong> 42P69B8</p>
                    <p><strong>Modelo: </strong> PA-1650-74</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/lucas%20rodrigues.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Lucas Rodrigues Alcassa</h3>
            <p><strong>Cargo: </strong> Analista de Negócios</p>
            <p><strong>Departamento: </strong> Inteligência de Negócio</p>
            <p><strong>E-mail: </strong> lucas.rodrigues@amorsaude.com</p>
            <p><strong>CPF: </strong> 312.280.518-95</p>
            <p><strong>Centro de Custo: </strong> 12010 - Inteligência de Negócios</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 234</p>
                    <p><strong>Série: </strong> H1KL0V3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510 (Win-10 i5-1135G7 8GB)</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 995</p>
                    <p><strong>Série: </strong> 2330AY30568</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 805</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 284</p>
                    <p><strong>Teclado S/N: </strong> 2243CE257939</p>
                    <p><strong>Mouse S/N: </strong> 2240LZN1JRY8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 403</p>
                    <p><strong>Série: </strong> 303AZTH94393</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/lucilene%20santos%20matias.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Lucilene Santos Matias</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Operações</p>
            <p><strong>E-mail: </strong> lucilene.matias@amorsaude.com</p>
            <p><strong>CPF: </strong> 219.665.528-74</p>
            <p><strong>Centro de Custo: </strong> 12006 - Regional 1</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 582</p>
                    <p><strong>Série: </strong> R9XT700F6NK</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 354985988580910/01</p>
                    <p><strong>IMEI 2: </strong> 355106278590811/01</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1451</p>
                    <p><strong>Série: </strong> CLBFD14</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3 (Win-11 i5-1235U 8GB)</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 879</p>
                    <p><strong>Série: </strong> 2316ME027FK9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 826</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 325</p>
                    <p><strong>Série: </strong> CN-ONCRCC-L0300-331-KOUH-A01</p>
                    <p><strong>Modelo: </strong> Dell KM5221W</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1478</p>
                    <p><strong>Série: </strong> 403AZmc6h747</p>
                    <p><strong>Modelo: </strong> LG-22MP410-B</p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> O colaborador está utilizando os equipamentos listados, porém apenas
                    possuímos o termo de responsabilidade do notebook, não tendo os termos nem os dados dos demais
                    equipamentos.</p>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/lucimara%20silva%20zampollo.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Lucimara Silva Zampollo</h3>
            <p><strong>Cargo: </strong> Recepcionista</p>
            <p><strong>Departamento: </strong> Financeiro</p>
            <p><strong>E-mail: </strong> lucimara.zampollo@amorsaude.com</p>
            <p><strong>CPF: </strong> 305.937.958-06</p>
            <p><strong>Subtituição:</strong>Lethicia Martins</p>
            <p><strong>Centro de Custo: </strong> 13008 - Recepção</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 950</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 362</p>
                    <p><strong>Série: </strong> 2238CE66388</p>
                    <p><strong>Modelo: </strong> MK270</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 383</p>
                    <p><strong>Série: </strong> CSFQXW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510 (Win-10 i5-1135G7 8GB)</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 387</p>
                    <p><strong>Série: </strong> 2235ME00yaj9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> N/A</p>
                    <p><strong>Série: </strong> R9Xw20CHGWN</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 35830223539235/01</p>
                    <p><strong>IMEI 2: </strong> 35976053925831/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Luiz Assumpção</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Operações</p>
            <p><strong>E-mail: </strong> luiz.assumpcao@amorsaude.com</p>
            <p><strong>CPF: </strong> 060.764.766-30</p>
            <p><strong>Centro de Custo: </strong> 12006 - Regional 1</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 224</p>
                    <p><strong>Série: </strong> 62KLOV3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 262</p>
                    <p><strong>Série: </strong> 2251CE5DBA8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 669</p>
                    <p><strong>Série: </strong> 00273964058</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 667</p>
                    <p><strong>Série: </strong> r9xta0efpll</p>
                    <p><strong>Modelo: </strong> N/A</p>
                    <p><strong>IMEI 1: </strong> 350916877023195/01</p>
                    <p><strong>IMEI 2: </strong> 354654237023193/01</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 819</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/luylla%20magalhaes.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Luylla Louise Magalhães</h3>
            <p><strong>Cargo: </strong> Coordenadora de Operações</p>
            <p><strong>Departamento: </strong> Operações</p>
            <p><strong>E-mail: </strong> luylla.magalhaes@amorsaude.com</p>
            <p><strong>CPF: </strong> 123.135.016-47</p>
            <p><strong>Centro de Custo: </strong> 18005 - Odontologia</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 948</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 479</p>
                    <p><strong>Série: </strong> 2232CE1CFB48</p>
                    <p><strong>Modelo: </strong> K270 - M185</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 888</p>
                    <p><strong>Série: </strong> 2316ME02F898</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 478</p>
                    <p><strong>Série: </strong> FB32JZ3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520 (Win 11 i5-1135G7 8GB)</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 959</p>
                    <p><strong>Série: </strong> ZF52489J2F</p>
                    <p><strong>Modelo: </strong> Moto E13</p>
                    <p><strong>IMEI 1: </strong> 359340983514354/05</p>
                    <p><strong>IMEI 2: </strong> 359340983514362/05</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/maicon%20leone%20alves%20facirolli.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Maicon Leone Alves Facirolli</h3>
            <p><strong>Cargo: </strong> Coordenador de Inteligência de Negócios</p>
            <p><strong>Departamento: </strong> Gerência Operações</p>
            <p><strong>E-mail: </strong> maicon.facirolli@amorsaude.com</p>
            <p><strong>CPF: </strong> 355.750.548-02</p>
            <p><strong>Centro de Custo: </strong> 12017 - Gerência Operações</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1573</p>
                    <p><strong>Série: </strong> HNSMC44</p>
                    <p><strong>Modelo: </strong> Notebook Gamer G15 (Win-11 i7-13650HX, GPU – 6GB, RAM-16GB, SSD-512GB)
                    </p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 766</p>
                    <p><strong>Série: </strong> 2125me0jypn8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 762</p>
                    <p><strong>Série: </strong> r9xt20134xz</p>
                    <p><strong>Modelo: </strong> N/A</p>
                    <p><strong>IMEI 1: </strong> 35985980028132/01</p>
                    <p><strong>IMEI 2: </strong> 355106270028133/01</p>
                </div>
                <div class="device">
                    <h4>🌐 Adaptador de Rede</h4>
                    <p><strong>Patrimônio: </strong> 1218</p>
                    <p><strong>Série: </strong> dlb075nw</p>
                    <p><strong>Modelo: </strong> N/A</p>
                </div>
                <div class="device">
                    <h4>📦 Case</h4>
                    <p><strong>Patrimônio: </strong> 573</p>
                    <p><strong>Série: </strong> 012136357015014788</p>
                    <p><strong>Modelo: </strong> C3 Tech/CH300Bk</p>
                </div>
                <div class="device">
                    <h4>💾 HD</h4>
                    <p><strong>Patrimônio: </strong> 572</p>
                    <p><strong>Série: </strong> Wxx1a3887pxx</p>
                    <p><strong>Modelo: </strong> Waster Digital/WD10spzx</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/malinda%20oliveira.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Malinda Gonçalves de Oliveira</h3>
            <p><strong>Cargo: </strong> Coordenadora de Atendimento ao Cliente</p>
            <p><strong>Departamento: </strong> Atendimento ao Cliente</p>
            <p><strong>E-mail: </strong> malinda.oliveira@amorsaude.com</p>
            <p><strong>CPF: </strong> 306.257.108-00</p>
            <p><strong>Centro de Custo: </strong> 12002 - Sac</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1528</p>
                    <p><strong>Série: </strong> PE0C4EE5</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3 IAP</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1114</p>
                    <p><strong>Série: </strong> 2433AYA011Y6</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>Adaptador de Rede</h4>
                    <p><strong>Patrimônio: </strong> 558</p>
                    <p><strong>Série: </strong> 2100403922834</p>
                    <p><strong>Modelo: </strong></p>
                </div>
                <div class="device">
                    <h4>📱 Smartphone</h4>
                    <p><strong>Patrimônio: </strong> 514</p>
                    <p><strong>Série: </strong> r9qt402h52a</p>
                    <p><strong>Modelo: </strong> Samsung A03</p>
                    <p><strong>IMEI 1: </strong> 351982711808031/01</p>
                    <p><strong>IMEI 2: </strong> 35329723128080/36</p>
                </div>
                <div class="device">
                    <h4>⌨️Teclado</h4>
                    <p><strong>Teclado Patrimônio: </strong> 1451</p>
                    <p><strong>Série: </strong> </p>
                    <p><strong>Modelo: </strong> Dell Pro Premium KB500</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Teclado Patrimônio: </strong> 1030</p>
                    <p><strong>Série: </strong> </p>
                    <p><strong>Modelo: </strong> Dell Pro MS300</p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> O colaborador está utilizando os equipamentos listados, porém ainda não
                    possui o termo de responsabilidade assinado para notebook.</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Marcela Diab Luiz</h3>
            <p><strong>Cargo: </strong> Coordenador de Operações</p>
            <p><strong>Departamento: </strong> Operações</p>
            <p><strong>E-mail: </strong> marcela.diab@amorsaude.com</p>
            <p><strong>CPF: </strong> 368.325.148-40</p>
            <p><strong>Centro de Custo: </strong> Não informado</p>
            <img src="../../media/collaborators/erica%20teodoro%20de%20oliveira%20da%20silva.jpg" alt="Foto de Erica Teodoro de Oliveira da Silva" class="profile-pic">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 166</p>
                    <p><strong>Série: </strong> PE09781x</p>
                    <p><strong>Modelo: </strong> Ideapad3 15alc6</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 530</p>
                    <p><strong>Série: </strong> 260307953</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 511</p>
                    <p><strong>Série: </strong> R9QT403WMHP</p>
                    <p><strong>Modelo: </strong> Samsung A03</p>
                    <p><strong>IMEI 1: </strong> 35198927132252/01</p>
                    <p><strong>IMEI 2: </strong> 35327231932257/01</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 578 - 185</p>
                    <p><strong>Série: </strong> 2211CE0A09B9</p>
                    <p><strong>Modelo: </strong> Logitech MK235</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> Não informado</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/marcelo%20martins.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Marcelo Alexandre Barbosa Martins</h3>
            <p><strong>Cargo: </strong> Analista de Suporte Jr</p>
            <p><strong>Departamento: </strong> Suporte</p>
            <p><strong>E-mail: </strong> marcelo.martins@amorsaude.com</p>
            <p><strong>CPF: </strong> 306.745.018-37</p>
            <p><strong>Centro de Custo: </strong> 13019</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1511</p>
                    <p><strong>Série: </strong> BGDTO14</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 998</p>
                    <p><strong>Série: </strong> 2330AY030ACB</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1508</p>
                    <p><strong>Série: </strong> 2345CE4BAA588</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1000</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Marcelo Borba Okazaki</h3>
            <p><strong>Cargo: </strong> Desenvolvedor Fullstack</p>
            <p><strong>Departamento: </strong> Desenvolvimento</p>
            <p><strong>E-mail: </strong> marcelo.okazaki@amorsaude.com</p>
            <p><strong>CPF: </strong> 452.967.358-89</p>
            <p><strong>Centro de Custo: </strong> 13012 - Desenvolvimento</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> O colaborador está utilizando os equipamentos, porém ainda não possui o
                    termo de responsabilidade assinado e dados dos mesmos.</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/marco%20bizarri.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Marco Aurélio Bizarri</h3>
            <p><strong>Cargo: </strong> Analista De Contas A Pagar Pl</p>
            <p><strong>Departamento: </strong> Financeiro</p>
            <p><strong>E-mail: </strong> marcos.bizarri@amorsaude.com</p>
            <p><strong>CPF: </strong> 412.562.488-78</p>
            <p><strong>Centro de Custo: </strong> 13001 - Financeiro</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1568</p>
                    <p><strong>Série: </strong> PE0CK1FF</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3 IAP</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1186 - 1176</p>
                    <p><strong>Série: </strong> 2344LZXBR3R8 (Teclado), 2347CE267DB8 (Mouse)</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1181</p>
                    <p><strong>Série: </strong> 2412AY00NY48</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1145</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>📺 Suporte Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1452</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Multi Suporte</p>
                </div>
                <div class="device">
                    <h4>🌐 Hub USB</h4>
                    <p><strong>Patrimônio: </strong> 1029</p>
                    <p><strong>Série: </strong> F202212-HC</p>
                    <p><strong>Modelo: </strong> Exbom</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/marcos%20corusse.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Marcos Henrique Corusse</h3>
            <p><strong>Cargo: </strong> Product Owner</p>
            <p><strong>Departamento: </strong> Produto</p>
            <p><strong>E-mail: </strong> marcos.corusse@amorsaude.com</p>
            <p><strong>CPF: </strong> 403.238.458-43</p>
            <p><strong>Centro de Custo: </strong> 13013 - Produto</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 269</p>
                    <p><strong>Série: </strong> 8JQN4T3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 280</p>
                    <p><strong>Série: </strong> 2243CE258409 (Teclado), 2241LZX3PA88 (Mouse)</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 834</p>
                    <p><strong>Série: </strong> 2309ME0ANX9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 823</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/marcos%20vinicius%20balerine%20e%20silva.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Marcos Vinicius Balerine e Silva</h3>
            <p><strong>Cargo: </strong>Coordenador Comercial</p>
            <p><strong>Departamento: </strong>Cirurgias</p>
            <p><strong>E-mail: </strong>marcos.silva@amorsaude.com</p>
            <p><strong>CPF: </strong>195.035.218-82</p>
            <p><strong>Centro de Custo: </strong>32000</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook I5 8gb ram 256gb ssd</h4>
                    <p><strong>Patrimônio: </strong>2008</p>
                    <p><strong>Série: </strong>PE0D1S44X</p>
                    <p><strong>Modelo: </strong>Lenovo V15 G3 IAP</p>
                </div>
                <div class="device">
                    <h4>⌨️Teclado</h4>
                    <p><strong>Patrimônio: </strong>2003</p>
                    <p><strong>Série: </strong>H4IB3121W</p>
                    <p><strong>Modelo: </strong>Dell KB3121WT</p>
                </div>
                <div class="device">
                    <h4>🖱️Mouse</h4>
                    <p><strong>Patrimônio: </strong>1090</p>
                    <p><strong>Série: </strong>H4IMS3121W</p>
                    <p><strong>Modelo: </strong>Dell MS3121WT</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>1100</p>
                    <p><strong>Série: </strong>2421AY01S7E8</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong>1099</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Aluminio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong>057</p>
                    <p><strong>Série: </strong>BR-0V4FTW-TVB00-1A6-80QB-A03</p>
                    <p><strong>Modelo: </strong>Dell E1920HF</p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> Sem termo de responsabilidade assinado do monitor</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/marcos%20vinicius%20moroti.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Marcos Vinicius Moroti</h3>
            <p><strong>Cargo: </strong> Assistente Administronline</p>
            <p><strong>Departamento: </strong> BackOffice</p>
            <p><strong>E-mail: </strong> marcos.moroti@amorsaude.com</p>
            <p><strong>CPF: </strong> 473.590.478-62</p>
            <p><strong>Centro de Custo: </strong> 12005 - BackOffice</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 248</p>
                    <p><strong>Série: </strong> 9JLQXWE</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 433</p>
                    <p><strong>Série: </strong> 224CE257B59 (Teclado), 2241lzc1uls8 (Mouse)</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1019</p>
                    <p><strong>Série: </strong> 2224MECKSFu9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 1020</p>
                    <p><strong>Série: </strong> R9XW20ChSXZ</p>
                    <p><strong>Modelo: </strong> Galaxy A03 Core</p>
                    <p><strong>IMEI 1: </strong> 358302235395843/01</p>
                    <p><strong>IMEI 2: </strong> 35976405395843/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <!--        Remover Licença     -->
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/maria%20medeiros.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Maria Caroline Dantas De Medeiros</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> maria.medeiros@amorsaude.com</p>
            <p><strong>CPF: </strong> 115.150.016-01</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 108</p>
                    <p><strong>Série: </strong> 6F3WQ23</p>
                    <p><strong>Modelo: </strong> Dell Inspiron 3584</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 761</p>
                    <p><strong>Série: </strong> 225942557</p>
                    <p><strong>Modelo: </strong> Jabra HS015</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 545</p>
                    <p><strong>Série: </strong> R9QT403WRSE</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 351989271933664/01</p>
                    <p><strong>IMEI 2: </strong> 353297231933669/01</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 16</p>
                    <p><strong>Série: </strong> H245463</p>
                    <p><strong>Modelo: </strong> Dell P2018HC</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/maria%20peterli.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Maria Eduarda da Silva Peterli</h3>
            <p><strong>Cargo: </strong> Assistente de CS</p>
            <p><strong>Departamento: </strong> Atendimento ao Cliente</p>
            <p><strong>E-mail: </strong> maria.peterli@amorsaude.com</p>
            <p><strong>CPF: </strong> 542.607.828-94</p>
            <p><strong>Centro de Custo: </strong> 12002 - SAC</p>
            <p><strong>Data de Admissão: </strong> 20.06.2022</p>
            <p><strong>Data de Desligamento: </strong> 03.09.2024</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 385</p>
                    <p><strong>Série: </strong> 3jILQXW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 808</p>
                    <p><strong>Série: </strong> 2232ME02TNX9</p>
                    <p><strong>Modelo: </strong> Logitech 370</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 809</p>
                    <p><strong>Série: </strong> R9XW20CHGPH</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 358302235392477/01</p>
                    <p><strong>IMEI 2: </strong> 359796405392473/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/maria%20luisa%20barros%20lima.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Maria Luisa Barros Lima</h3>
            <p><strong>Cargo: </strong> Assistente De Canais Digitais E Relacionamento</p>
            <p><strong>Departamento: </strong> Canais Digitais e Relacionamento</p>
            <p><strong>E-mail: </strong> maria.lima@amorsaude.com</p>
            <p><strong>CPF: </strong> 456.010.168-01</p>
            <p><strong>Centro de Custo: </strong> 22001 - Consulta Assistida</p>
            <p><strong>Data de Admissão: </strong> 02.12.2024</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 934</p>
                    <p><strong>Série: </strong> 2323GG15JD8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 442</p>
                    <p><strong>Série: </strong> 307AZMG9G027</p>
                    <p><strong>Modelo: </strong> LG 22MP410</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 03</p>
                    <p><strong>Série: </strong> 2tn05l3</p>
                    <p><strong>Modelo: </strong> Dell Inspiron 3501 p90f</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 412</p>
                    <p><strong>Série: </strong> jnzr0036</p>
                    <p><strong>Modelo: </strong> Logitech K235</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 816</p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/marianne%20lesur%20goncalves.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Marianne Lesur Goncalves</h3>
            <p><strong>Cargo: </strong> Coordenadora de Qualidade de Telemedicina</p>
            <p><strong>Departamento: </strong> Telemedicina</p>
            <p><strong>E-mail: </strong> marianne.goncalves@amorsaude.com</p>
            <p><strong>CPF: </strong> 366.240.078-26</p>
            <p><strong>Centro de Custo: </strong> 22001 - Consulta Assistida</p>
            <p><strong>Data de Admissão: </strong> 02.10.2023</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 886</p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 887</p>
                    <p><strong>Série: </strong> 2316ME027D39</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 431</p>
                    <p><strong>Série: </strong> 4VZHNY3</p>
                    <p><strong>Modelo: </strong> Dell Latitude 3420</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 594</p>
                    <p><strong>Série: </strong> R9XT700H5nn</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 932</p>
                    <p><strong>Série: </strong> R9XW108WZZT</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 432</p>
                    <p><strong>Série: </strong> CN-ONCRCC-LO300-331-KOUB-A01</p>
                    <p><strong>Modelo: </strong> Dell KM5221</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>

        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/marjorie%20caetano%20rodrigues.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Marjorie Caetano Rodrigues</h3>
            <p><strong>Cargo: </strong> Product Owner</p>
            <p><strong>Departamento: </strong> Produtos</p>
            <p><strong>E-mail: </strong> marjorie.rodrigues@amorsaude.com</p>
            <p><strong>CPF: </strong> 148.006.727-08</p>
            <p><strong>Centro de Custo: </strong> 13013 - Produto</p>
            <p><strong>Data de Admissão: </strong> 17.11.2020</p>
            <p><strong>Data da Promoção: </strong> 01.11.2024</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 161</p>
                    <p><strong>Série: </strong> PE0973a2</p>
                    <p><strong>Modelo: </strong> Lenovo Ideapad 3-15ALC6</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1102</p>
                    <p><strong>Série: </strong> 2421AY020S28</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 2012</p>
                    <p><strong>Série: </strong> CN-08JV2Y-LO300-43C-K2F0-A07</p>
                    <p><strong>Modelo: </strong> Dell KB3121WT</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Mateus Rodrigues Nistarda</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> mateus.nistarda@amorsaude.com</p>
            <p><strong>CPF: </strong> 395.947.118-17</p>
            <p><strong>Centro de Custo: </strong> Não informado</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 042</p>
                    <p><strong>Série: </strong> bq1sfm2</p>
                    <p><strong>Modelo: </strong> Dell Inspiron 14p75g</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 768</p>
                    <p><strong>Série: </strong> 00027313315020917</p>
                    <p><strong>Modelo: </strong> Jabra HSC015</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 041</p>
                    <p><strong>Série: </strong> JM92C03</p>
                    <p><strong>Modelo: </strong> Dell P2018HC</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong>589</p>
                    <p><strong>Série: </strong> R9XT703HHHL</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 354985989157890/01</p>
                    <p><strong>IMEI 2: </strong> 355106279157891/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Matheus Lima</h3>
            <p><strong>Cargo: </strong>Coordenador de Parcerias Comercias</p>
            <p><strong>Departamento: </strong> Cirurgias</p>
            <p><strong>E-mail: </strong> matheus.lima@amorsaude.com</p>
            <p><strong>CPF: </strong> 229.650.428-06</p>
            <p><strong>Centro de Custo: </strong> Não informado</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 049</p>
                    <p><strong>Série: </strong>6BTR3L3</p>
                    <p><strong>Modelo: </strong> Dell Inspiron 5402</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/mathiele%20matos.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Mathiele Machado de Matos</h3>
            <p><strong>Cargo: </strong> Consultoria de Operações</p>
            <p><strong>Departamento: </strong> Não informado</p>
            <p><strong>E-mail: </strong> mathiele.matos@amorsaude.com</p>
            <p><strong>CPF: </strong> 008.422.160-73</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 229</p>
                    <p><strong>Série: </strong> C4KL0V3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 677</p>
                    <p><strong>Série: </strong> 273963681</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 673</p>
                    <p><strong>Série: </strong> R9XTA0efpb</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 350916877023070/01</p>
                    <p><strong>IMEI 2: </strong> 354654237023078/01</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 326 - 707</p>
                    <p><strong>Série: </strong> 2250CE0D2759 - 2240LZX5K868</p>
                    <p><strong>Modelo: </strong> Logitech Mk235</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 725</p>
                    <p><strong>Série: </strong> Não informado</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/miguel%20silverio.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Miguel Silverio</h3>
            <p><strong>Cargo: </strong>Analista de Suporte Jr</p>
            <p><strong>Departamento: </strong>Suporte</p>
            <p><strong>E-mail: </strong>miguel.silverio@amorsaude.com</p>
            <p><strong>CPF: </strong>441.067.438-25</p>
            <p><strong>Centro de Custo: </strong>13019 - Suporte</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>1512</p>
                    <p><strong>Série: </strong>25Y3F14</p>
                    <p><strong>Modelo: </strong>Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong>1509</p>
                    <p><strong>Série: </strong>2345CE4BA548</p>
                    <p><strong>Modelo: </strong>Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>996</p>
                    <p><strong>Série: </strong>23330Y0321BB</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong>997</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Alumínio</p>
                </div>

            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%23foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Milena Cristina Fonseca Perandini</h3>
            <p><strong>Cargo:</strong>Consultor(a) de Operações</p>
            <p><strong>Departamento:</strong></p>
            <p><strong>E-mail:</strong>milena.perandini@amorsaude.com</p>
            <p><strong>CPF:</strong>460.393.908-67</p>
            <p><strong>Centro de Custo:</strong>12007 - Regional 2</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio:</strong>268</p>
                    <p><strong>Série:</strong>JN3MOT3</p>
                    <p><strong>Modelo:</strong>Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio:</strong>292</p>
                    <p><strong>Série:</strong>2243CE258109 - 2240LZN1KFU8</p>
                    <p><strong>Modelo:</strong>K270 - M185</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio:</strong>1031</p>
                    <p><strong>Série:</strong>2232ME02UL99</p>
                    <p><strong>Modelo:</strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio:</strong>931</p>
                    <p><strong>Série:</strong></p>
                    <p><strong>Modelo:</strong>Alumnio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio:</strong>010</p>
                    <p><strong>Série:</strong>BR-OV4FTW-TVB00-1468QGB-A03</p>
                    <p><strong>Modelo:</strong>Dell E1920H</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio:</strong>541</p>
                    <p><strong>Série:</strong>R9QT403E6BX</p>
                    <p><strong>Modelo:</strong>Galaxy A03</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Miria Elisiane Cardoso</h3>
            <p><strong>Cargo: </strong> Assistente de CS</p>
            <p><strong>Departamento: </strong> Atendimento ao Cliente</p>
            <p><strong>E-mail: </strong> miria.cardozo@amorsaude.com</p>
            <p><strong>CPF: </strong> 419.539.578-03</p>
            <p><strong>Centro de Custo: </strong> 12002 - SAC</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 639</p>
                    <p><strong>Série: </strong> R9xt9071v2z</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 209</p>
                    <p><strong>Série: </strong> PE09xvkk</p>
                    <p><strong>Modelo: </strong> Lenovo V15 82ME</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 915</p>
                    <p><strong>Série: </strong> 2232ME0208J8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 349 - 350</p>
                    <p><strong>Série: </strong> 2234CE182249 - 1130LZN0ZWz8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 333</p>
                    <p><strong>Série: </strong> Não informado</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/monica%20goncalves%20soares.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Monica Gonçalves Soares</h3>
            <p><strong>Cargo: </strong> Recepcionista</p>
            <p><strong>Departamento: </strong> Financeiro</p>
            <p><strong>E-mail: </strong> monica.soares@amorsaude.com</p>
            <p><strong>CPF: </strong> 305.937.958-06</p>
            <p><strong>Centro de Custo: </strong> 13000 - Dir. Financ</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 950</p>
                    <p><strong>Série: </strong> Não informado</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 362</p>
                    <p><strong>Série: </strong> 2238CE66388</p>
                    <p><strong>Modelo: </strong> MK270</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 383</p>
                    <p><strong>Série: </strong> CSFQXW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 387</p>
                    <p><strong>Série: </strong> 2235ME00yaj9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> Não informado</p>
                    <p><strong>Série: </strong> R9Xw20CHGWN</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 35830223539235/01</p>
                    <p><strong>IMEI 2: </strong> 35976053925831/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/murillo%20zanine.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Murillo Flavio Zanine</h3>
            <p><strong>Cargo: </strong> Analista Financeiro</p>
            <p><strong>Departamento: </strong> Financeiro</p>
            <p><strong>E-mail: </strong> murillo.zanine@amorsaude.com</p>
            <p><strong>CPF: </strong> 448.038.248-86</p>
            <p><strong>Centro de Custo: </strong> 13001 - Financeiro</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 288</p>
                    <p><strong>Série: </strong> 6hqn4t3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 492</p>
                    <p><strong>Série: </strong> 2229LZXL7GF8</p>
                    <p><strong>Modelo: </strong> Logitech K270 - M185</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 916</p>
                    <p><strong>Série: </strong> 2332ME0207N8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 917</p>
                    <p><strong>Série: </strong> Não informado</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1463</p>
                    <p><strong>Série: </strong> 401AZFM5Y043</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/murilo%20borba.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Murilo Samuel Borba</h3>
            <p><strong>Cargo: </strong> Analista de Dados Jr.</p>
            <p><strong>Departamento: </strong> Dados</p>
            <p><strong>E-mail: </strong> murilo.borba@amorsaude.com</p>
            <p><strong>CPF: </strong> 443.463.578-65</p>
            <p><strong>Centro de Custo: </strong> 13011 - Dados</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 963</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1441</p>
                    <p><strong>Série: </strong> 2232CE1D0268</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 697</p>
                    <p><strong>Série: </strong> 276340866</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 64</p>
                    <p><strong>Série: </strong> PE08JF8X</p>
                    <p><strong>Modelo: </strong> Lenovo ThinkPad E14 Gen 3</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/murilo%20venturoso.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Murilo Venturoso Tinazzo</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> 12006 - Regional 1</p>
            <p><strong>E-mail: </strong> murilo.tinazzo@amorsaude.com</p>
            <p><strong>CPF: </strong> 334.823.378-02</p>
            <p><strong>Centro de Custo: </strong> 12006 - Regional 1</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1558</p>
                    <p><strong>Série: </strong> PE0C2WYM-82UM007BR-JKCN42WW</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3 IAP</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1120 - 1559</p>
                    <p><strong>Série: </strong> 2344LZXBR448 - 2347CE268068</p>
                    <p><strong>Modelo: </strong> Logitech MK279</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1153</p>
                    <p><strong>Série: </strong> 2412AY00NR38</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1154</p>
                    <p><strong>Série: </strong> Não fornecido</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 427</p>
                    <p><strong>Série: </strong> 304AZCQ6Z283</p>
                    <p><strong>Modelo: </strong> LG22MP410-B</p>
                </div>
                <div class="device">
                    <h4>📱 Smartphone</h4>
                    <p><strong>Patrimônio: </strong> 1224</p>
                    <p><strong>Série: </strong> UI5MVA3PBL</p>
                    <p><strong>Modelo: </strong> Moto E22</p>
                    <p><strong>IMEI 1: </strong> 352800824221094</p>
                    <p><strong>IMEI 2: </strong> 352800824221102</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Natalia Duarte Zimmermann</h3>
            <p><strong>Cargo: </strong> Coordenadora de Operações</p>
            <p><strong>Departamento: </strong> Consultorria</p>
            <p><strong>E-mail: </strong> natalia.zimmermann@amorsaude.com</p>
            <p><strong>CPF: </strong> 142.180.767-07</p>
            <p><strong>Centro de Custo: </strong> 12006 - Regional 2</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 166</p>
                    <p><strong>Série: </strong> PE0981X</p>
                    <p><strong>Modelo: </strong> Ideapad3 15alc6</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 692</p>
                    <p><strong>Série: </strong> 27536253</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 185 - 578</p>
                    <p><strong>Série: </strong> 2207LZX4F248</p>
                    <p><strong>Modelo: </strong> Logitech MK235</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 511</p>
                    <p><strong>Série: </strong> R9QT403WMHP</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 35198927132252/01</p>
                    <p><strong>IMEI 2: </strong> 3532723193225/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/natanel%20zavanela.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Natanael Zavanela</h3>
            <p><strong>Cargo: </strong> Consultor De Operações</p>
            <p><strong>Departamento: </strong> Regional 1</p>
            <p><strong>E-mail: </strong> natanael.zavanela@amorsaude.com</p>
            <p><strong>CPF: </strong> 330.324458-84</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regiona 2</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🔌 Adaptador de Rede</h4>
                    <p><strong>Patrimônio: </strong> 1216</p>
                    <p><strong>Série: </strong> 223B92011087</p>
                    <p><strong>Modelo: </strong> UE300C</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 889</p>
                    <p><strong>Série: </strong> 2320AY099929</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 168</p>
                    <p><strong>Série: </strong> pe097838</p>
                    <p><strong>Modelo: </strong> Dell Ideapad3 15alc6 Ryzen5-5500u Win-10 8Gb 256GB</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1498</p>
                    <p><strong>Série: </strong> 2232CE1C7128 - 2229LZK3028</p>
                    <p><strong>Modelo: </strong> Logitech MK270 - M170</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 739</p>
                    <p><strong>Série: </strong> r9xta0ecssv</p>
                    <p><strong>Modelo: </strong> Samsung A03</p>
                    <p><strong>IMEI 1: </strong> 350916876991574/01</p>
                    <p><strong>IMEI 2: </strong> 354654236991572/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/nicholas%20lopes.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Nicholas Esdras Lopes dos Santos</h3>
            <p><strong>Cargo: </strong> Assistente de BackOffice</p>
            <p><strong>Departamento: </strong> BackOffice - Treinamentos</p>
            <p><strong>E-mail: </strong> nicholas.lopes@amorsaude.com</p>
            <p><strong>CPF: </strong> 462.083.518-80</p>
            <p><strong>Centro de Custo: </strong> 12005</p>
            <p><strong>Substituição: </strong> Letícia Godoy da Silva</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 5</p>
                    <p><strong>Série: </strong> 17N0513</p>
                    <p><strong>Modelo: </strong> Dell Inspiron 3501</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 953</p>
                    <p><strong>Série: </strong> 2329AY019B08</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 962</p>
                    <p><strong>Série: </strong> Não fornecido</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 716</p>
                    <p><strong>Série: </strong> R9XT202F3JW</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 354985980107183/01</p>
                    <p><strong>IMEI 2: </strong> 355106270107184/01</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 182 - 571</p>
                    <p><strong>Série: </strong> 2211CE0A2F69</p>
                    <p><strong>Modelo: </strong> MK235 - M170</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/nicolas%20tassinari.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Nicolas Francisco Eloy Tassinari</h3>
            <p><strong>Cargo: </strong> Analista De Processos</p>
            <p><strong>Departamento: </strong> BackOffice</p>
            <p><strong>E-mail: </strong> nicolas.tassinari@amorsaude.com</p>
            <p><strong>CPF: </strong> 450.326.278-52</p>
            <p><strong>Centro de Custo: </strong> 12005 - Backoffice</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1458</p>
                    <p><strong>Série: </strong> 2239CE14C8C8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 395</p>
                    <p><strong>Série: </strong> 303AZZX8K061</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 25</p>
                    <p><strong>Série: </strong> h7lyxp3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 1535P112F</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 780</p>
                    <p><strong>Série: </strong> 2125me0jz328</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 579</p>
                    <p><strong>Série: </strong> R9XT700ELMH</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 354985988574632/01</p>
                    <p><strong>IMEI 2: </strong> 355106278574633/01</p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> O colaborador está utilizando os equipamentos listados, porém ainda não
                    possui o termo de responsabilidade assinado para os demais itens, exceto pelo kit de teclado, mouse
                    e monitor.</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/nicole%20souza.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Nicole Souza Cimento</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> nicole.souza@amorsaude.com</p>
            <p><strong>CPF: </strong> 464.476.338.19</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 282</p>
                    <p><strong>Série: </strong> 2243CE258429 - 2241LZX3P288</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 271</p>
                    <p><strong>Série: </strong> 2P3MOT3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 835</p>
                    <p><strong>Série: </strong> 2309ME017VY8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 846</p>
                    <p><strong>Série: </strong> UJ5MVC3MT8</p>
                    <p><strong>Modelo: </strong> Motorola E13</p>
                    <p><strong>IMEI 1: </strong> 35934098125427</p>
                    <p><strong>IMEI 2: </strong> 359340981254284</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1245</p>
                    <p><strong>Série: </strong> (Não fornecido)</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>

        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/otavio%20freitas.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Otavio Honrorio de Freitas</h3>
            <p><strong>Cargo: </strong>Product Owner Junior</p>
            <p><strong>Departamento: </strong>Projetos e Processos</p>
            <p><strong>E-mail: </strong>otavio.freitas@amorsaude.com</p>
            <p><strong>CPF: </strong>431.693.188-10</p>
            <p><strong>Centro de Custo: </strong>13004 - Projetos / Processos</p>
            <p><span class=" online">: online</span></p>
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>190</p>
                    <p><strong>Série: </strong>pe09ftjn</p>
                    <p><strong>Modelo: </strong>Lenovo Ideapad3 15alc6</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong>237</p>
                    <p><strong>Série: </strong>2253CE0F1789 - 2248LZX6ND08</p>
                    <p><strong>Modelo: </strong>Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>601</p>
                    <p><strong>Série: </strong>265441361</p>
                    <p><strong>Modelo: </strong>Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong>803</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong>397</p>
                    <p><strong>Série: </strong>303AZPU94384</p>
                    <p><strong>Modelo: </strong>LG 22MP410-B</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/pamela%20cristina%20sene%20sousa%20crispin.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Pamela Cristina Sene Souza Crispin</h3>
            <p><strong>Cargo: </strong> Assistente de Atendimento ao Cliente</p>
            <p><strong>Departamento: </strong> Atendimento ao Cliente</p>
            <p><strong>E-mail: </strong> pamela.crispin@amorsaude.com</p>
            <p><strong>CPF: </strong> 418.479.688-56</p>
            <p><strong>Centro de Custo: </strong> 12002 - SAC</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1161</p>
                    <p><strong>Série: </strong> 2316GG00AST8</p>
                    <p><strong>Modelo: </strong> Logitech</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1172</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 2018</p>
                    <p><strong>Série: </strong> PE0CMYKR</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3 IAP</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 1053</p>
                    <p><strong>Série: </strong> PB3B0010CR</p>
                    <p><strong>Modelo: </strong> Moto E14</p>
                    <p><strong>IMEI 1: </strong> 354679909903115</p>
                    <p><strong>IMEI 2: </strong> 354673303303123</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1053</p>
                    <p><strong>Série: </strong> CN-08JV2Y-LO300-43C-K2KJ-A07</p>
                    <p><strong>Modelo: </strong> Dell KB3121WT</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/Pamela%20Felix%20Divino.png" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Pamela Felix Divino</h3>
            <p><strong>Cargo: </strong> Analista De Processos E Documentações</p>
            <p><strong>Departamento: </strong> BackOffice</p>
            <p><strong>E-mail: </strong></p>
            <p><strong>CPF: </strong> 364.688.988-89</p>
            <p><strong>Centro de Custo: </strong> 12005 - Backoffice</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 161</p>
                    <p><strong>Série: </strong> PE0973a2</p>
                    <p><strong>Modelo: </strong> Lenovo Ideapad 3-15ALC6</p>
                </div>

                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 379</p>
                    <p><strong>Série: </strong> Teclado: 2240ce0b5479, Mouse: 2237lzxa0b48</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 898</p>
                    <p><strong>Série: </strong> 2332ME021N79</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📶 Adaptador de Rede</h4>
                    <p><strong>Patrimônio:</strong>1038</p>
                    <p><strong>Série:</strong></p>
                    <p><strong>Modelo:</strong>UL-1200</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 1246</p>
                    <p><strong>Série: </strong> zf5248dnzl</p>
                    <p><strong>Modelo: </strong> Moto E13</p>
                    <p><strong>IMEI 1: </strong> 354187776540152</p>
                    <p><strong>IMEI 2: </strong> 354187776540160</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 403</p>
                    <p><strong>Série: </strong> 303AZTH94393</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/paula%20tagliacolli.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Paula Aparecida Tagliacolli</h3>
            <p><strong>Cargo: </strong> Analista De Pessoas E Cultura</p>
            <p><strong>Departamento: </strong> Pessoas & Cultura</p>
            <p><strong>E-mail: </strong> paula.tagliacolli@amorsaude.com</p>
            <p><strong>CPF: </strong></p>
            <p><strong>Centro de Custo: </strong> 13006</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 217</p>
                    <p><strong>Série: </strong> G4KLOV3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 652</p>
                    <p><strong>Série: </strong> 273963705</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong> R9XTA0ectdd</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 350916876991772/01</p>
                    <p><strong>IMEI 2: </strong> 354654236991770/01</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 990</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 438</p>
                    <p><strong>Série: </strong> Teclado: 2241LZX1UFU8, Mouse: 2243CE255CF8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Paulo Antônio Imon Spanó</h3>
            <p><strong>Cargo: </strong> Desenvolvedor</p>
            <p><strong>Departamento: </strong> Desenvolvimento</p>
            <p><strong>E-mail: </strong> paulo.spano@amorsaude.com</p>
            <p><strong>CPF: </strong> 395.033.398-39</p>
            <p><strong>Centro de Custo: </strong> 13015 - Desenvolvimento</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 279</p>
                    <p><strong>Série: </strong> Teclado: 2243CE2583F9, Mouse: 2241LZX3PAS8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 829</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 838</p>
                    <p><strong>Série: </strong> 2309ME013N79</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 254</p>
                    <p><strong>Série: </strong> BHQN4T3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Paulo Gabriel Silva Araújo Gomes</h3>
            <p><strong>Cargo: </strong>Engenheiro de Dados</p>
            <p><strong>Departamento: </strong>Dados</p>
            <p><strong>E-mail: </strong>paulo.gomes@amorsaude.com</p>
            <p><strong>CPF: </strong>020.782.122-45</p>
            <p><strong>Centro de Custo: </strong>13015 - Gerência Dados</p>
            <p><strong>Subtituição: </strong>Victor Ribeiro da Silva Dantas</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>65</p>
                    <p><strong>Série: </strong>3BBTQK3</p>
                    <p><strong>Modelo: </strong>Dell Vostro 3400</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>1077</p>
                    <p><strong>Série: </strong>2433AYE00NG8</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong>202 - 629</p>
                    <p><strong>Série: </strong>JNZYR0036</p>
                    <p><strong>Modelo: </strong>Logitech K235</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong>360</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Alumínio</p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> Coletar Termo de Equipamentos</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/paulo%20rick.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Paulo Joneas Goncalves Rick</h3>
            <p><strong>Cargo: </strong> Product Owner</p>
            <p><strong>Departamento: </strong> Não especificado</p>
            <p><strong>E-mail: </strong> paulo.rick@amorsaude.com</p>
            <p><strong>CPF: </strong> Não fornecido</p>
            <p><strong>Centro de Custo: </strong> 13013 - Produto</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1051</p>
                    <p><strong>Série: </strong> 2421AY021MB8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> O colaborador está utilizando o equipamento, porém ainda não possui o termo
                    de responsabilidade assinado e dados do mesmo.</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/paulo%20victor%20borges.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Paulo Victor Borges</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> paulo.borges@amorsaude.com</p>
            <p><strong>CPF: </strong> 370.549.518-67</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1210</p>
                    <p><strong>Série: </strong> 2331AY020448</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1209</p>
                    <p><strong>Série: </strong> Não fornecido</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1530</p>
                    <p><strong>Série: </strong> 2347CE270488</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1531</p>
                    <p><strong>Série: </strong> PE9014228074</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 1229</p>
                    <p><strong>Série: </strong> UI5MVA3QHK</p>
                    <p><strong>Modelo: </strong> Moto E22</p>
                    <p><strong>IMEI 1: </strong> 352800824239559</p>
                    <p><strong>IMEI 2: </strong> 352800824239567</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 426</p>
                    <p><strong>Série: </strong> 304AZER6R692</p>
                    <p><strong>Modelo: </strong> LG-22MP410-B</p>
                </div>
                <div class="device">
                    <h4>⚡ Fonte</h4>
                    <p><strong>Patrimônio: </strong> 1208</p>
                    <p><strong>Série: </strong> 41X6766</p>
                    <p><strong>Modelo: </strong> PA-1650-74</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/pedro%20daher%20yunes.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Pedro Daher Yunes</h3>
            <p><strong>Cargo: </strong> Analista de Marketing Internacional</p>
            <p><strong>Departamento: </strong> Marketing Internacional</p>
            <p><strong>E-mail: </strong> pedro.yunes@amorsaude.com</p>
            <p><strong>CPF: </strong> 859.418.242-20</p>
            <p><strong>Centro de Custo: </strong> 14006 - Marketing Internacional</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 381</p>
                    <p><strong>Série: </strong> HRFQXW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 811</p>
                    <p><strong>Série: </strong> 2235ME00Y2R9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 810</p>
                    <p><strong>Série: </strong> R9XW20CHL4K</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 35830223539608/01</p>
                    <p><strong>IMEI 2: </strong> 35976405393604/01</p>
                </div>
            </div>
            <!-- Termo de Responsabilidade sempre aparece -->
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/pedro%20castelani.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Pedro Henrique Castelani Dias Pires do Prado</h3>
            <p><strong>Cargo: </strong> Analista De QA</p>
            <p><strong>Departamento: </strong> Produto</p>
            <p><strong>E-mail: </strong> pedro.castelani@amorsaude.com</p>
            <p><strong>CPF: </strong> [Não fornecido]</p>
            <p><strong>Centro de Custo: </strong> 13013</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <!-- Sem dispositivos fornecidos -->
            </div>
            <!-- Termo de Responsabilidade sempre aparece -->
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Pedro Henrique Cruz Ubine</h3>
            <p><strong>Cargo: </strong> Estagiário(a) Dev</p>
            <p><strong>Departamento: </strong> Desenvolvimento</p>
            <p><strong>E-mail: </strong> pedro.ubine@amorsaude.com</p>
            <p><strong>CPF: </strong> 419.484.238-39</p>
            <p><strong>Centro de Custo: </strong> 13012</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <!-- Sem dispositivos fornecidos -->
            </div>
            <!-- Termo de Responsabilidade sempre aparece -->
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/pedro%20henrique%20romano%20caetano.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Pedro Henrique Romano Caetano</h3>
            <p><strong>Cargo: </strong> Analista De Processos Jr</p>
            <p><strong>Departamento: </strong> Backoffice</p>
            <p><strong>E-mail: </strong> pedro.caetano@amorsaude.com</p>
            <p><strong>CPF: </strong> 455.371.238-51</p>
            <p><strong>Centro de Custo: </strong> 12005 - Backoffice</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 265</p>
                    <p><strong>Série: </strong> 1Q3MOT3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 468</p>
                    <p><strong>Série: </strong> Não fornecido</p>
                    <p><strong>Modelo: </strong> Dell - MS300</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 836</p>
                    <p><strong>Série: </strong> 2309ME01ARP9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> O colaborador está utilizando o equipamento monitor, porém ainda não possui
                    o termo de responsabilidade assinado para o mesmo.</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/pedro%20nicolas.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Pedro Nícolas Vieira</h3>
            <p><strong>Cargo: </strong> Estágio Suporte</p>
            <p><strong>Departamento: </strong> Suporte</p>
            <p><strong>E-mail: </strong> pedro.vierira@amorsaude.com</p>
            <p><strong>CPF: </strong> 459.765.178-03</p>
            <p><strong>Centro de Custo: </strong> 13019 - Suporte</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1510</p>
                    <p><strong>Série: </strong> 5GDT014</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1513</p>
                    <p><strong>Série: </strong> 2345CE4BA598</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1001</p>
                    <p><strong>Série: </strong> Não fornecido</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1002</p>
                    <p><strong>Série: </strong> 2330AYO304CB</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio:</strong>039</p>
                    <p><strong>Série:</strong>BR-OOCPG1-TVB00-04E-2FIU-A08</p>
                    <p><strong>Modelo:</strong>Dell P2018HC</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/pricila%20santana.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Pricila Françoaze Carrile Santana</h3>
            <p><strong>Cargo: </strong>Especialista Financeiro </p>
            <p><strong>Departamento: </strong>Financeiro</p>
            <p><strong>E-mail: </strong> pricila.santana@amorsaude.com</p>
            <p><strong>CPF: </strong> 300.611.718-24</p>
            <p><strong>Centro de Custo: </strong>13001 - Financeiro</p>
            <p><strong>Subtituição: </strong>Barbara Bidurin</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1462</p>
                    <p><strong>Série: </strong> 9V9VD14</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>🎧 Headset</h4>
                    <p><strong>Patrimônio: </strong> 784</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1460</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Dell KM5221W</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1233</p>
                    <p><strong>Série: </strong> Não fornecido</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong>802</p>
                    <p><strong>Série: </strong>R9XW20CHGWN</p>
                    <p><strong>Modelo: </strong>Galaxy A03 Core</p>
                    <p><strong>IMEI 1: </strong>35830223592535</p>
                    <p><strong>IMEI 2: </strong>35976440539231</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/priscila%20souza.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Priscila Batista De Souza</h3>
            <p><strong>Cargo: </strong>Assistente de CS</p>
            <p><strong>Departamento: </strong>Atendimento ao Cliente</p>
            <p><strong>E-mail: </strong>priscila.souza@amorsaude.com</p>
            <p><strong>CPF: </strong>333.873.878-14</p>
            <p><strong>Centro de Custo: </strong>12002 - SAC</p>
            <p><strong>Subtituição: </strong>Isadora Soares Marques</p>
            <p><strong>Data de Demissão: </strong></p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>227</p>
                    <p><strong>Série: </strong>64KLOV3</p>
                    <p><strong>Modelo: </strong>Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong>343 - 344</p>
                    <p><strong>Série: </strong>2234CE1821B9 - 2230LZN0ZP58</p>
                    <p><strong>Modelo: </strong>Logitech MK370</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>675</p>
                    <p><strong>Série: </strong>273961372</p>
                    <p><strong>Modelo: </strong>Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong>734</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Alumínio</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>;
                    <p><strong>Patrimônio: </strong>671</p>
                    <p><strong>Série: </strong>R9XTA0ECZCW</p>
                    <p><strong>Modelo: </strong>Galaxy A03</p>
                    <p><strong>IMEI 1: </strong>350916876993414/01</p>
                    <p><strong>IMEI 2: </strong>354654236993412/01</p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> Veriifcar Monitor e Coletar Termo</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/priscila%20faquim.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Priscila Moreira Faquim</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> priscila.faqim@amorsaude.com</p>
            <p><strong>CPF: </strong> 346.070.918-98</p>
            <p><strong>Centro de Custo: </strong> 12006 - Regional 1</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 477</p>
                    <p><strong>Série: </strong> BD35KZ3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3520</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 946</p>
                    <p><strong>Série: </strong> 2329A YOOB8808</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 936</p>
                    <p><strong>Série: </strong> Não fornecido</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> Não fornecido</p>
                    <p><strong>Série: </strong> Não fornecido</p>
                    <p><strong>Modelo: </strong> Dell KB121WT</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 451</p>
                    <p><strong>Série: </strong> 308AZXC8A098</p>
                    <p><strong>Modelo: </strong> LG 22MP410</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/rafael%20gomes.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Rafael Rodrigues Cordeiro Gomes</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> rafael.gomes@amorsaude.com</p>
            <p><strong>CPF: </strong> 402.847.248-23</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 46</p>
                    <p><strong>Série: </strong> 6F50R23</p>
                    <p><strong>Modelo: </strong> Dell Inspiron 3584</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 768</p>
                    <p><strong>Série: </strong> 2399829109</p>
                    <p><strong>Modelo: </strong> Jabra HSC 015</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 969</p>
                    <p><strong>Série: </strong> Não fornecido</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 422</p>
                    <p><strong>Série: </strong> 304AZPU6R704</p>
                    <p><strong>Modelo: </strong> LG 22MP410</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1447</p>
                    <p><strong>Série: </strong> 2229LZXK4618</p>
                    <p><strong>Modelo: </strong> MK270</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>

        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/rafaela%20silva.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Rafaela Domenici da Silva</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Operações</p>
            <p><strong>E-mail: </strong> rafaela.domenici@amorsaude.com</p>
            <p><strong>CPF: </strong> 407.827.468-45</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 219</p>
                    <p><strong>Série: </strong> 83KLOV3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>⌨️️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 708</p>
                    <p><strong>Série: </strong> 2250CE0D2709 - 2240LZX5j3C8</p>
                    <p><strong>Modelo: </strong> Logitech MK235</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 654</p>
                    <p><strong>Série: </strong> 274048216</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 726</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>💻 Celular</h4>
                    <p><strong>Patrimônio: </strong> 590</p>
                    <p><strong>Série: </strong> R9XT700HLMM</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 354985988607309/01</p>
                    <p><strong>IMEI 2: </strong> 355106278607300/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Raíssa Martins de Lima</h3>
            <p><strong>Cargo: </strong> Assistente de Retenção</p>
            <p><strong>Departamento: </strong> Relacionamento com Cliente</p>
            <p><strong>E-mail: </strong> raissa.lima@amorsaude.com</p>
            <p><strong>CPF: </strong> 374.117.888-80</p>
            <p><strong>Centro de Custo: </strong> 72023 - Relacionamento com Cliente / Retenção</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1555</p>
                    <p><strong>Série: </strong> PE9012326044-82UM0007BR-PE0CB4ZP</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3 IAP</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1560 - 1195</p>
                    <p><strong>Série: </strong> 2347CE268038 - 2344LZXBR4M8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1164</p>
                    <p><strong>Série: </strong> 2412AYOONXEB</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📝 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/raphael%20leite.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Raphael Leite</h3>
            <p><strong>Cargo:</strong>Designer UI/UX</p>
            <p><strong>Departamento:</strong>Produto</p>
            <p><strong>E-mail:</strong>raphael.leite@amorsaude.com</p>
            <p><strong>CPF:</strong>028.739.429-83</p>
            <p><strong>Centro de Custo:</strong>13013 - Produto</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio:</strong>476</p>
                    <p><strong>Série:</strong>3932JZ3</p>
                    <p><strong>Modelo:</strong>Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio:</strong>475</p>
                    <p><strong>Série:</strong>2232CE1D02A8 - 2230LZX3BEEE8</p>
                    <p><strong>Modelo:</strong>Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio:</strong>945</p>
                    <p><strong>Série:</strong>2329AYOOCA38</p>
                    <p><strong>Modelo:</strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio:</strong>944</p>
                    <p><strong>Série:</strong></p>
                    <p><strong>Modelo:</strong>Aluminio</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/renan%20brito%20freire.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Renan Brito Freire</h3>
            <p><strong>Cargo: </strong> Analista de Documentações</p>
            <p><strong>Departamento: </strong> BackOffice</p>
            <p><strong>E-mail: </strong> renan.freire@amorsaude.com</p>
            <p><strong>CPF: </strong> 439.638.938-85</p>
            <p><strong>Centro de Custo: </strong> 12005 - Backoffice</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 382</p>
                    <p><strong>Série: </strong> GRFQXW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 566</p>
                    <p><strong>Série: </strong> r9xr500mwvm</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A10s</p>
                    <p><strong>IMEI 1: </strong> 350879696004905/01</p>
                    <p><strong>IMEI 2: </strong> 359753826004906/01</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1524</p>
                    <p><strong>Série: </strong> 2334CE2DA838 - 2331LZXRR4CE</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 390</p>
                    <p><strong>Série: </strong> 303AZQV96524</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 391</p>
                    <p><strong>Série: </strong> 303AZSP96526</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 364</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📝 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/renata%20aparecida%20ferraccini%20gomes.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Renata Aparecida Ferraccini Gomes</h3>
            <p><strong>Cargo: </strong> Analista De Contas A Pagar Pl</p>
            <p><strong>Departamento: </strong> Financeiro</p>
            <p><strong>E-mail: </strong> renata.gomes@amorsaude.com</p>
            <p><strong>CPF: </strong> 282 846 148-39</p>
            <p><strong>Centro de Custo: </strong> 13001 - Financeiro</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 275</p>
                    <p><strong>Série: </strong> 2243ce258478</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 815</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 166</p>
                    <p><strong>Série: </strong> pe09781x</p>
                    <p><strong>Modelo: </strong> ideapad3 15alc6</p>
                </div>
                <div class="device">
                    <h4>🎧 Headset</h4>
                    <p><strong>Patrimônio: </strong> 1017</p>
                    <p><strong>Série: </strong> 2235ME00Z1J9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1452</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Multi Suporte</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/renata%20merlo.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Renata Sousa Merlo</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> renata.merlo@amorsaude.com</p>
            <p><strong>CPF: </strong> 443.414.268-26</p>
            <p><strong>Centro de Custo: </strong> 12006 - Regional 1</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 103</p>
                    <p><strong>Série: </strong> 6f2yq23</p>
                    <p><strong>Modelo: </strong> Dell Inspiron 15P75F</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 460</p>
                    <p><strong>Série: </strong> 2232CE1C7278 - 2227LZX8GVS8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 925</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Ricardo Antonio Perdigão</h3>
            <p><strong>Cargo: </strong> Coordenador Regional de Operações</p>
            <p><strong>Departamento: </strong> Operações</p>
            <p><strong>E-mail: </strong> ricardo.perdigao@amorsaude.com</p>
            <p><strong>CPF: </strong> 352.074.958-01</p>
            <p><strong>Centro de Custo: </strong> 12019 - Gerência Regional 2</p>
            <img class="status" src="../imagem/icone/offpng" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 155</p>
                    <p><strong>Série: </strong> 1614sn3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 791</p>
                    <p><strong>Série: </strong> 00054596695220419</p>
                    <p><strong>Modelo: </strong> Jabra HSC015</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 016</p>
                    <p><strong>Série: </strong> h245463</p>
                    <p><strong>Modelo: </strong> Dell P2018HC</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 720</p>
                    <p><strong>Série: </strong> R9QT403WQCR</p>
                    <p><strong>Modelo: </strong> Samung Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 351989271933193/01</p>
                    <p><strong>IMEI 2: </strong> 353297231933198/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Rodrigo Prado Pelicano</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> rodrigo.pelicano@amorsaude.com</p>
            <p><strong>CPF: </strong> 345.200.218-71</p>
            <p><strong>Centro de Custo: </strong></p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 244</p>
                    <p><strong>Série: </strong> 46FQXW3</p>
                    <p><strong>Modelo: </strong> Dell/Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Headset</h4>
                    <p><strong>Patrimônio: </strong> 1012</p>
                    <p><strong>Série: </strong> 2224MECKSA9</p>
                    <p><strong>Modelo: </strong> Logitech/H390</p>
                </div>
                <div class="device">
                    <h4>📱 Smartphone</h4>
                    <p><strong>Patrimônio: </strong> 1011</p>
                    <p><strong>Série: </strong> R9XW20CHKG</p>
                    <p><strong>Modelo: </strong> Galaxy A03 Core</p>
                    <p><strong>IMEI 1: </strong> 358302235393392/01</p>
                    <p><strong>IMEI 2: </strong> 359796405393398/01</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 728</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Kit Teclado</h4>
                    <p><strong>Patrimônio: </strong> 329</p>
                    <p><strong>Série: </strong> 2250ce0d2719 / 2240lzx5k858</p>
                    <p><strong>Modelo: </strong> MK235</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Roger Ricobelo Mazzali</h3>
            <p><strong>Cargo: </strong> Analista de QA Pl</p>
            <p><strong>Departamento: </strong> Produtos</p>
            <p><strong>E-mail: </strong> roger.mazzali@amorsaude.com</p>
            <p><strong>CPF: </strong> 319.054.418-27</p>
            <p><strong>Centro de Custo: </strong> 13019</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 795</p>
                    <p><strong>Série: </strong> LM-X 120BMW</p>
                    <p><strong>Modelo: </strong> K8+</p>
                    <p><strong>IMEI 1: </strong> 353523117417273</p>
                    <p><strong>IMEI 2: </strong> 353523117417281</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1052</p>
                    <p><strong>Série: </strong> 2501AY4008Z8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Rogerio Santos de Almeida</h3>
            <p><strong>Cargo: </strong> Gerente Financeiro</p>
            <p><strong>Departamento: </strong> Financeiro</p>
            <p><strong>E-mail: </strong> rogerio.almeida@amorsaude.com</p>
            <p><strong>CPF: </strong>075.248.096-03</p>
            <p><strong>Centro de Custo: </strong> 13000 - Dir. Financeira</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🔌 Adaptador de Rede</h4>
                    <p><strong>Patrimônio: </strong> 614</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong></p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 843</p>
                    <p><strong>Série: </strong> UI5HVE5P7T</p>
                    <p><strong>Modelo: </strong> Moto E22</p>
                    <p><strong>IMEI 1: </strong> 355165984901423</p>
                    <p><strong>IMEI 2: </strong> 355165984901415</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 600</p>
                    <p><strong>Série: </strong> 2654439793</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 189</p>
                    <p><strong>Série: </strong> PE09FTTP</p>
                    <p><strong>Modelo: </strong> Ideapad3 15AL6</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/rosemeire%20aparecida%20vieira%20soares.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Rosemeire Aparecida Vieira Soares</h3>
            <p><strong>Cargo: </strong> Recepção</p>
            <p><strong>Departamento: </strong> Facilities</p>
            <p><strong>E-mail: </strong> rosemeire.soares@amorsaude.com</p>
            <p><strong>CPF: </strong> 167.232.588-94</p>
            <p><strong>Centro de Custo: </strong> 13008 - Recepção</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 274</p>
                    <p><strong>Série: </strong> 2243CE258488 - 2241LZX3P0V8</p>
                    <p><strong>Modelo: </strong> MK270</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 156</p>
                    <p><strong>Série: </strong> 54W2QD3</p>
                    <p><strong>Modelo: </strong> Dell Inspiron 3501</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 814</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 387</p>
                    <p><strong>Série: </strong> S/N</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/rubem%20diniz.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Rubem Diniz</h3>
            <p><strong>Cargo: </strong> Analista de Suporte Pl</p>
            <p><strong>Departamento: </strong> Suporte</p>
            <p><strong>E-mail: </strong> rubem.diniz@amorsaude.com</p>
            <p><strong>CPF: </strong> N/A</p>
            <p><strong>Centro de Custo: </strong> 13019</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⚠️ Informações Importantes</h4>
                    <p><strong>Alerta: </strong> O colaborador está utilizando equipamentos sem registro detalhado e sem
                        o termo de responsabilidade assinado.</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/rubia%20albuquerque.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Rubia Camila Albuquerque Dos Santos</h3>
            <p><strong>Cargo: </strong> Gerente de Atendimento ao Cliente</p>
            <p><strong>Departamento: </strong> Atendimento ao Cliente</p>
            <p><strong>E-mail: </strong> rubia.albuquerque@amorsaude.com</p>
            <p><strong>CPF: </strong> 301.676.228-55</p>
            <p><strong>Centro de Custo: </strong> 12000 - Dir. Operações</p>
            <img class="status" src="../../media/icon/on.png" alt="">

            <div>

            </div>
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 470</p>
                    <p><strong>Série: </strong> 2232CE1D0298 - 2230LZX3BNQ8</p>
                    <p><strong>Modelo: </strong> K270 - M185</p>
                </div>
                <div class="device">
                    <h4>🎧 Headset</h4>
                    <p><strong>Patrimônio: </strong> 941</p>
                    <p><strong>Série: </strong> 2329GG016JC8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1433</p>
                    <p><strong>Série: </strong> 3124AZSP0Z422</p>
                    <p><strong>Modelo: </strong> LG/22MP410-B</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 486</p>
                    <p><strong>Série: </strong> 7932JZ3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1455</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Multi Suporte</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sairah%20fonseca%20da%20silva.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Saírah Fonseca da Silva</h3>
            <p><strong>Cargo: </strong> Consultora de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> sairah.silva@amorsaude.com</p>
            <p><strong>CPF: </strong> 096.801.206-00</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 220</p>
                    <p><strong>Série: </strong> 52klov3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 655</p>
                    <p><strong>Série: </strong> r9xta0ecn2t</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 350916876990014/01</p>
                    <p><strong>IMEI 2: </strong> 354654236990012/01</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1203</p>
                    <p><strong>Série: </strong> 2331AY037208</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> Teclado: 328, Mouse: 723</p>
                    <p><strong>Série: </strong> Teclado: 2250CE0D27C9, Mouse: 2240LZX5MKN8</p>
                    <p><strong>Modelo: </strong> Logitech MK235</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 727</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 392</p>
                    <p><strong>Série: </strong> 303AZXC94258</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sandra%20oliveira.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Sandra Andreia de Oliveira</h3>
            <p><strong>Cargo: </strong> Gerente de Pessoas</p>
            <p><strong>Departamento: </strong> Pessoas e Cultura</p>
            <p><strong>E-mail: </strong> sandra.oliveira@amorsaude.com</p>
            <p><strong>CPF: </strong> 175.483.388-80</p>
            <p><strong>Centro de Custo: </strong> 13021 - Gerência Pessoas e Cultura</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 109</p>
                    <p><strong>Série: </strong> JTJYXP3</p>
                    <p><strong>Modelo: </strong> Dell Vostro P11f</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 892</p>
                    <p><strong>Série: </strong> 2316ME02F888</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 472</p>
                    <p><strong>Série: </strong> 3074zafmod555</p>
                    <p><strong>Modelo: </strong> LG222Mp410</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 538</p>
                    <p><strong>Série: </strong> R9XT2013C3l</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 354985980030500/01</p>
                    <p><strong>IMEI 2: </strong> 355106270030501/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sara%20domingos.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Sara Martins Domingos</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong>Consultoria</p>
            <p><strong>E-mail: </strong>sara.domingos@amorsaude.com</p>
            <p><strong>CPF: </strong> 464.717.258-92</p>
            <p><strong>Centro de Custo: </strong>12006 - Regional 1</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 837</p>
                    <p><strong>Série: </strong> 2309ME013N89</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 828</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 541</p>
                    <p><strong>Série: </strong> R9QT403e6bx</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 351989271894098/01</p>
                    <p><strong>IMEI 2: </strong> 353297231894093/01</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 292</p>
                    <p><strong>Série: </strong> 2243CE258109 - 2240LZN1KU8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 268</p>
                    <p><strong>Série: </strong> JN3MOT3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 422</p>
                    <p><strong>Série: </strong> 304AZPU6R704</p>
                    <p><strong>Modelo: </strong> LG 22MP410</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/simone%20rodrigues.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Simone Karina de Araujo Fabres Rodrigues</h3>
            <p><strong>Cargo: </strong> Assistente de CS</p>
            <p><strong>Departamento: </strong> SAC</p>
            <p><strong>E-mail: </strong></p>
            <p><strong>CPF: </strong> 326.384.048-70</p>
            <p><strong>Centro de Custo: </strong></p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 335 (Teclado), 336 (Mouse)</p>
                    <p><strong>Série: </strong> 2234CE1821C9 - 2230LZN0ZN68</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 730</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 75</p>
                    <p><strong>Série: </strong> 5NVMKW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 690</p>
                    <p><strong>Série: </strong> 275244184</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 687</p>
                    <p><strong>Série: </strong> R9XTAB4EQN</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 352081309333965/01</p>
                    <p><strong>IMEI 2: </strong> 357299759333963/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sophia%20fantini.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Sophia Vieira Fantini</h3>
            <p><strong>Cargo: </strong> Estagiário</p>
            <p><strong>Departamento: </strong> BackOffice - Documentações</p>
            <p><strong>E-mail: </strong> sophia.fantini@amorsaude.com</p>
            <p><strong>CPF: </strong> 398.365.638-22</p>
            <p><strong>Centro de Custo: </strong> 12005 - BackOffice</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1556</p>
                    <p><strong>Série: </strong> PE0C2WYY</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3 IAP</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1197</p>
                    <p><strong>Série: </strong> 2412AY00K6F9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1196</p>
                    <p><strong>Série: </strong> (Não fornecido)</p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1554</p>
                    <p><strong>Série: </strong> 2347CE268058</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/suellen%20tavares.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Suellen Aparecida Crispim Tavares</h3>
            <p><strong>Cargo: </strong> Analista Comercial</p>
            <p><strong>Departamento: </strong> Amor Cirurgias</p>
            <p><strong>E-mail: </strong> suellen.tavares@amorsaude.com</p>
            <p><strong>CPF: </strong> 343.634.398-66</p>
            <p><strong>Centro de Custo: </strong> 32001 - Administronline</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 13</p>
                    <p><strong>Série: </strong> G7LYXP3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 682</p>
                    <p><strong>Série: </strong> 27396821</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 718</p>
                    <p><strong>Série: </strong> r9qr1082gxh</p>
                    <p><strong>Modelo: </strong> Samsung A02s</p>
                    <p><strong>IMEI 1: </strong> 355473601371420/01</p>
                    <p><strong>IMEI 2: </strong> 359454181371424/01</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 495</p>
                    <p><strong>Série: </strong> 2323LXX5KT18 - 2325CE138068</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> Informação relevante aqui</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/tabata%20fernades.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Tabata Fernades</h3>
            <p><strong>Cargo: </strong>Analista Administrativo Jr</p>
            <p><strong>Departamento: </strong>Plano Odontológico</p>
            <p><strong>E-mail: </strong>tabata.fernandes@amorsaude.com</p>
            <p><strong>CPF: </strong>336.174.488-10</p>
            <p><strong>Centro de Custo: </strong>72024 - Operações Plano</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong>1557</p>
                    <p><strong>Série: </strong>PE0CK1FH</p>
                    <p><strong>Modelo: </strong>Lenovo V15 G3 IAP</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong>1188 - 1187</p>
                    <p><strong>Série: </strong>2344LZXBR3L8 - 2347CE267DA8</p>
                    <p><strong>Modelo: </strong>Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong>1180</p>
                    <p><strong>Série: </strong>2412AY00P198</p>
                    <p><strong>Modelo: </strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong>1155</p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Aluminio</p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> Informação relevante aqui</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/tamara%20saturno.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Tamara Honorato Saturno</h3>
            <p><strong>Cargo: </strong> Coordenadora de Suporte</p>
            <p><strong>Departamento: </strong> Suporte</p>
            <p><strong>E-mail: </strong> tamara.saturno@amorsaude.com</p>
            <p><strong>CPF: </strong> 368.245.228-13</p>
            <p><strong>Centro de Custo: </strong> 13019 - Suporte</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 167</p>
                    <p><strong>Série: </strong> PE09782g</p>
                    <p><strong>Modelo: </strong> Ideapad3 15ALC6</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Modelo:</strong>Moto E14</p>
                    <p><strong>Serie:</strong>ZY22K5M9JM</p>
                    <p><strong>Patrimonio:</strong>1171</p>
                    <p><strong>IMEI 1:</strong>354673303308718/07</p>
                    <p><strong>IMEI 2:</strong>354673303308726/07</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1519</p>
                    <p><strong>Série: </strong> 2334CE2DE2C8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1214</p>
                    <p><strong>Série: </strong> 235CAYO51F08</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1014</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Tatiane Coelho da Luz</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Operações</p>
            <p><strong>E-mail: </strong> tatiane.luz@amorsaude.com</p>
            <p><strong>CPF: </strong> 346.405.008-40</p>
            <p><strong>Centro de Custo: </strong> 12006 - Regional 1</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 224</p>
                    <p><strong>Série: </strong> 62KLOV3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 262</p>
                    <p><strong>Série: </strong> 2251CE5DBA8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 669</p>
                    <p><strong>Série: </strong> 00273964058</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 667</p>
                    <p><strong>Série: </strong> r9xta0efpll</p>
                    <p><strong>IMEI 1: </strong> 350916877023195/01</p>
                    <p><strong>IMEI 2: </strong> 354654237023193/01</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 819</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/tatiane%20zeotti.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Tatiane Tavaes Zeott</h3>
            <p><strong>Cargo: </strong> Assistente de Treinamento</p>
            <p><strong>Departamento: </strong> Não informado</p>
            <p><strong>E-mail: </strong> Não informado</p>
            <p><strong>CPF: </strong> 365.396.218-84</p>
            <p><strong>Centro de Custo: </strong> Não informado</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 369</p>
                    <p><strong>Série: </strong> Não fornecido</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 377</p>
                    <p><strong>Série: </strong> 2240CE0B4E19</p>
                    <p><strong>Modelo: </strong> Logitech Mk235</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 394</p>
                    <p><strong>Série: </strong> 303AZCQ93931</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/thaina%20soares.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Thaina Soares de Jesus</h3>
            <p><strong>Cargo: </strong> Não informado</p>
            <p><strong>Departamento: </strong> Não informado</p>
            <p><strong>E-mail: </strong> Não informado</p>
            <p><strong>CPF: </strong> 380.231.428-01</p>
            <p><strong>Centro de Custo: </strong> Não informado</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 153</p>
                    <p><strong>Série: </strong> DQK44W2</p>
                    <p><strong>Modelo: </strong> Dell Inspiron 15</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 719</p>
                    <p><strong>Série: </strong> 0002313268020917</p>
                    <p><strong>Modelo: </strong> Jabra HSC015</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 154</p>
                    <p><strong>Série: </strong> 17GH2W2</p>
                    <p><strong>Modelo: </strong> Dell P2018HF</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Thais Cristina Medina Whitehead</h3>
            <p><strong>Cargo: </strong> Consultora de Operações</p>
            <p><strong>Departamento: </strong> Não informado</p>
            <p><strong>E-mail: </strong> Não informado</p>
            <p><strong>CPF: </strong> 423.069.398-29</p>
            <p><strong>Centro de Custo: </strong> Não informado</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 295</p>
                    <p><strong>Série: </strong> 1P3M0T3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1031</p>
                    <p><strong>Série: </strong> 2232ME02UL99</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 427</p>
                    <p><strong>Série: </strong> 304AZCQ6Z283</p>
                    <p><strong>Modelo: </strong> LG-22M410</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 844</p>
                    <p><strong>Série: </strong> SVN8D86404</p>
                    <p><strong>Modelo: </strong> Motorola E13</p>
                    <p><strong>IMEI 1: </strong> 359340981262659</p>
                    <p><strong>IMEI 2: </strong> 359340981262667</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 297</p>
                    <p><strong>Série: </strong> 2243CE2581119 - 2240LZN1KFY8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/thais%20medina.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Thais Dias Martins</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> thais.martins@amorsaude.com</p>
            <p><strong>CPF: </strong> 184.66.578-78</p>
            <p><strong>Centro de Custo: </strong> 12006 - Regioan 1</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 3510</p>
                    <p><strong>Série: </strong> 1p3m0t3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1031</p>
                    <p><strong>Série: </strong> 2232ME02UL99</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 426</p>
                    <p><strong>Série: </strong> 304AZER6R692</p>
                    <p><strong>Modelo: </strong> LG 22M410</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 844</p>
                    <p><strong>Série: </strong> SNV8D86404</p>
                    <p><strong>Modelo: </strong> Motorola E13</p>
                    <p><strong>IMEI 1: </strong> 359340981262659</p>
                    <p><strong>IMEI 2: </strong> 359340981262667</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 297</p>
                    <p><strong>Série: </strong> 2243CE258119 - 2240LZN1KF8Y</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/thiago%20isaac%20da%20silva.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Thiago Isaac da Silva</h3>
            <p><strong>Cargo: </strong> Analista De Suporte Pl</p>
            <p><strong>Departamento: </strong> Suporte</p>
            <p><strong>E-mail: </strong> thiago.silva@amorsaude.com</p>
            <p><strong>CPF: </strong> 436.577.728-98</p>
            <p><strong>Centro de Custo: </strong> 13019 - Suporte</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 312</p>
                    <p><strong>Série: </strong> DTZHY3</p>
                    <p><strong>Modelo: </strong> Dell Latitude 3420</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1198</p>
                    <p><strong>Série: </strong> 2316gg00at18</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 318-851</p>
                    <p><strong>Série: </strong> 331-KOUI-AO1</p>
                    <p><strong>Modelo: </strong> KM5221W</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 862</p>
                    <p><strong>Série: </strong> Não fornecido</p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Tiago Amado</h3>
            <p><strong>Cargo:</strong>Analista de Dados</p>
            <p><strong>Departamento:</strong>Dados</p>
            <p><strong>E-mail:</strong>tiago.amado@amorsaude.com</p>
            <p><strong>CPF:</strong></p>
            <p><strong>Centro de Custo:</strong></p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio:</strong>065</p>
                    <p><strong>Série:</strong>3bbtqk3</p>
                    <p><strong>Modelo:</strong>Dell Vostro 3400 P132G</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio:</strong>741</p>
                    <p><strong>Série:</strong>2125me0k0568</p>
                    <p><strong>Modelo:</strong>Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio:</strong>066</p>
                    <p><strong>Série:</strong>y4skhx5t300155p</p>
                    <p><strong>Modelo:</strong>Samsung F24T350FHLS</p>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/vanessa%20posca.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Vanessa Barbosa Posca</h3>
            <p><strong>Cargo: </strong> Coordenadora de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> vanessa.posca@amorsaude.com</p>
            <p><strong>CPF: </strong> 219.607.248-60</p>
            <p><strong>Centro de Custo: </strong> 12019 - Gerência Regional 2</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 155</p>
                    <p><strong>Série: </strong> 1614sn3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 875</p>
                    <p><strong>Série: </strong> 2315AY07AC29</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> Não fornecido</p>
                    <p><strong>Série: </strong> Não fornecido</p>
                    <p><strong>Modelo: </strong> Aluminio</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 844</p>
                    <p><strong>Série: </strong> SVN8D86304</p>
                    <p><strong>Modelo: </strong> Motorola E13</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/vanessa%20de%20sousa%20matos.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Vanessa de Souza Matos</h3>
            <p><strong>Cargo: </strong> Assistente de Atendimento ao Cliente</p>
            <p><strong>Departamento: </strong> Atendimento ao Cliente</p>
            <p><strong>E-mail: </strong> vanessa.matos@amorsaude.com</p>
            <p><strong>CPF: </strong> 478.525.748-23</p>
            <p><strong>Centro de Custo: </strong> 12002 - SAC</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 385</p>
                    <p><strong>Série: </strong> 3jILQXW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 808</p>
                    <p><strong>Série: </strong> 2232ME02TNX9</p>
                    <p><strong>Modelo: </strong> Logitech 370</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong></p>
                    <p><strong>Série: </strong></p>
                    <p><strong>Modelo: </strong>Aluminio</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 809</p>
                    <p><strong>Série: </strong> R9XW20CHGPH</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 358302235392477/01</p>
                    <p><strong>IMEI 2: </strong> 359796405392473/01</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 292</p>
                    <p><strong>Série: </strong> 2243CE258108</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> O colaborador está utilizando os equipamentos listados, porém apenas
                    possuímos o termo de responsabilidade do teclado e mouse, não tendo os termos para o notebook, fone
                    e celular.</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/vanessa%20fraga.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Vanessa Lutz Fraga</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> [não fornecido]</p>
            <p><strong>CPF: </strong> 017.671.550-93</p>
            <p><strong>Centro de Custo: </strong> 12003 - Relacionamento Profissionais da Saúde</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 500 (Teclado) / 334 (Mouse)</p>
                    <p><strong>Série: </strong> 2234CE1821E9 (Teclado) / 2230LZN0ZZAB (Mouse)</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 702</p>
                    <p><strong>Série: </strong> Não fornecido</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> O colaborador está utilizando os equipamentos listados, porém apenas
                    possuímos o termo de responsabilidade do teclado, mouse e suporte, não tendo os termos nem os dados
                    dos demais equipamentos.</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/victor%20souza.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Victor Conceição de Souza</h3>
            <p><strong>Cargo: </strong> Analista de Endomarketing</p>
            <p><strong>Departamento: </strong> Marketing Interno</p>
            <p><strong>E-mail: </strong> victor.souza@amorsaude.com</p>
            <p><strong>CPF: </strong> 037.234.715-01</p>
            <p><strong>Centro de Custo: </strong> 14002 - Endomarketing</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 74</p>
                    <p><strong>Série: </strong> 9nvmkw3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 691</p>
                    <p><strong>Série: </strong> 275420378</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 688</p>
                    <p><strong>Série: </strong> R9XTA0D3X6F</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 352081309577132/01</p>
                    <p><strong>IMEI 2: </strong> 357299759588130/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/victor%20hugo.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Victor Hugo De Souza</h3>
            <p><strong>Cargo: </strong> Estagiário(a) Dev</p>
            <p><strong>Departamento: </strong> Desenvolvimento</p>
            <p><strong>E-mail: </strong> victor.hugo@amorsaude.com</p>
            <p><strong>CPF: </strong> 478.319.538-25</p>
            <p><strong>Centro de Custo: </strong> 13012 - Desenvolvimento</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 246</p>
                    <p><strong>Série: </strong> GSFQXW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1015</p>
                    <p><strong>Série: </strong> 2235ME00Y1B9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 1016</p>
                    <p><strong>Série: </strong> R9XW20CHLBE</p>
                    <p><strong>Modelo: </strong> Galaxy A03 Core</p>
                    <p><strong>IMEI 1: </strong> 358302235393679/01</p>
                    <p><strong>IMEI 2: </strong> 35976405393679/01</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 277</p>
                    <p><strong>Série: </strong> 2243CE258459 - 2241LZX3PAE8</p>
                    <p><strong>Modelo: </strong> Logitech Mk270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 825</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/victor%20lima.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Victor Hugo Souza Lima</h3>
            <p><strong>Cargo: </strong> Product Owner</p>
            <p><strong>Departamento: </strong> CRM</p>
            <p><strong>E-mail: </strong> victor.lima@amorsaude.com</p>
            <p><strong>CPF: </strong> 397.234.398-16</p>
            <p><strong>Centro de Custo: </strong> 14003 - CRM</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 246</p>
                    <p><strong>Série: </strong> GSFQXW3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1015</p>
                    <p><strong>Série: </strong> 2235ME00Y1B9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> N/A</p>
                    <p><strong>Série: </strong> R9XW20CHLBE</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 35830223593673/01</p>
                    <p><strong>IMEI 2: </strong> 35976405393679/01</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1003</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Teclado - Patrimônio: </strong> 1979</p>
                    <p><strong>Série: </strong> CN-08JV2Y-LO300-48U-K4F4-A07</p>
                    <p><strong>Modelo: </strong> Dell KM5221W</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/victor%20dantas.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Victor Ribeiro da Silva Dantas</h3>
            <p><strong>Cargo: </strong> Engenheiro de Dados</p>
            <p><strong>Departamento: </strong> Dados</p>
            <p><strong>E-mail: </strong> victor.dantas@amorsaude.com</p>
            <p><strong>CPF: </strong> 060.444.161-43</p>
            <p><strong>Centro de Custo: </strong> 13011 - Dados</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 360</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Teclado - Patrimônio: </strong> 202</p>
                    <p><strong>Mouse - Patrimônio: </strong> 629</p>
                    <p><strong>Série: </strong> JNZYR0036</p>
                    <p><strong>Modelo: </strong> Logitech K235</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 547</p>
                    <p><strong>Série: </strong> 2128ME0EYNA8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 65</p>
                    <p><strong>Série: </strong> 3BBTQK3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3400</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/vinicius%20massullo.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Vinicius Massullo Silva</h3>
            <p><strong>Cargo: </strong> Gerente De Dados</p>
            <p><strong>Departamento: </strong> Gerência Tecnologia</p>
            <p><strong>E-mail: </strong> vinicius.massullo@amorsaude.com</p>
            <p><strong>CPF: </strong> pj3.271.625/0001-09</p>
            <p><strong>Centro de Custo: </strong> 13015</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 298</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 301</p>
                    <p><strong>Série: </strong> 2243CE255CC9 - 2241LZX1UDQ8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 819</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 300</p>
                    <p><strong>Série: </strong> 304AZHY70369</p>
                    <p><strong>Modelo: </strong> LG 22MP410</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 717</p>
                    <p><strong>Série: </strong> 2310ME00YUA9</p>
                    <p><strong>Modelo: </strong> Samsung Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 354985980029693</p>
                    <p><strong>IMEI 2: </strong> 355106280029693</p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> O colaborador está utilizando os equipamentos, porém ainda não possui o
                    termo de responsabilidade assinado e dados dos mesmos.</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/sem%20foto.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Vinicius Matheus Barreto Novaes</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> N/A</p>
            <p><strong>CPF: </strong> 425.036.598-02</p>
            <p><strong>Centro de Custo: </strong> N/A</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 214</p>
                    <p><strong>Série: </strong> C3KLOV3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 6649</p>
                    <p><strong>Série: </strong> 274044668</p>
                    <p><strong>Modelo: </strong> Jabra HSC0016</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 414</p>
                    <p><strong>Série: </strong> 303AZY395250</p>
                    <p><strong>Modelo: </strong> LG 22MP410</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 660</p>
                    <p><strong>Série: </strong> R9XTA0ECERX</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 350916876987606/01</p>
                    <p><strong>IMEI 2: </strong> 354654236987604/01</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 818</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/Vitor%20Magno.png" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Vitor Magno Da Silva</h3>
            <p><strong>Cargo: </strong> Desenvolvedor</p>
            <p><strong>Departamento: </strong> Desenvolvimento</p>
            <p><strong>E-mail: </strong> vitor.magno@amorsaude.com</p>
            <p><strong>CPF: </strong> 459.273.348-73</p>
            <p><strong>Centro de Custo: </strong> 13012 - Desenvolvimento</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 415</p>
                    <p><strong>Série: </strong> 5VZHNY3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3110</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 867</p>
                    <p><strong>Série: </strong> 2315AY07A1A9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 418</p>
                    <p><strong>Série: </strong> CN-ONCRCC-LO300-331-KOVH-A01</p>
                    <p><strong>Modelo: </strong> Dell KM5221W</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 871</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/Viviane%20Carvalho.png" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Viviane de Carvalho</h3>
            <p><strong>Cargo: </strong> Consultoria de Operações</p>
            <p><strong>Departamento: </strong> N/A</p>
            <p><strong>E-mail: </strong> viviane.carvalho@amorsaude.com</p>
            <p><strong>CPF: </strong> 339.344.308-99</p>
            <p><strong>Centro de Custo: </strong> 12007 - Regional 2</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 272</p>
                    <p><strong>Série: </strong> 4P3MOT3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 15 3510</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 283</p>
                    <p><strong>Série: </strong> 2243CE258439 - 2241LZX3P7V8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 816</p>
                    <p><strong>Série: </strong> N/A</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 839</p>
                    <p><strong>Série: </strong> 2309ME01AQT9</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong>589</p>
                    <p><strong>Série: </strong> R9XT703HHHL</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 354985989157890/01</p>
                    <p><strong>IMEI 2: </strong> 355106279157891/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/Sem%20Foto.png" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Viviane Fernanda da Silva Murari</h3>
            <p><strong>Cargo: </strong> Consultora de Operações</p>
            <p><strong>Departamento: </strong> Operações</p>
            <p><strong>E-mail: </strong> viviane.murari@amorsaude.com</p>
            <p><strong>CPF: </strong> 369.419.328-64</p>
            <p><strong>Centro de Custo: </strong> N/A</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 212</p>
                    <p><strong>Série: </strong> PE09EEK9</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G2</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 646</p>
                    <p><strong>Série: </strong> 273748566</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/Walkiria%20Silva.png" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Walkiria Oliveira Silva</h3>
            <p><strong>Cargo: </strong> Analista Adminsitrativo PL</p>
            <p><strong>Departamento: </strong> Operações Plano</p>
            <p><strong>E-mail: </strong> walkiria.silva@amorsaude.com</p>
            <p><strong>CPF: </strong> 067.368.296-07</p>
            <p><strong>Centro de Custo: </strong> 72024 - Operações Plano</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 921</p>
                    <p><strong>Série: </strong> 2316ME02F8H8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 490</p>
                    <p><strong>Série: </strong> 2308LZX49CD8 - 2310CE1A2BB8</p>
                    <p><strong>Modelo: </strong> Logitech M185 - K270</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 810</p>
                    <p><strong>Série: </strong> R9XW20CHL4K</p>
                    <p><strong>Modelo: </strong> Galaxy A03 Core</p>
                    <p><strong>IMEI 1: </strong> 358302235393608</p>
                    <p><strong>IMEI 2: </strong> 359796405393604</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 489</p>
                    <p><strong>Série: </strong> HS35KZ3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/Wendell%20Oliveira.png" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Wendell Alessandro Candido Oliveira</h3>
            <p><strong>Cargo: </strong> Analista de Pessoas & Cultura Sênior</p>
            <p><strong>Departamento: </strong> Pessoas & Cultura</p>
            <p><strong>E-mail: </strong> wendell.oliveira@amorsaude.com</p>
            <p><strong>CPF: </strong> 411.412.878-62</p>
            <p><strong>Centro de Custo: </strong> 13006 - Pessoas & Cultura</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1446</p>
                    <p><strong>Série: </strong> B832JZ3</p>
                    <p><strong>Modelo: </strong> Dell Vostro 3520</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 966</p>
                    <p><strong>Série: </strong> 2329AYO1A558</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1445</p>
                    <p><strong>Série: </strong> 2232CE1CFB58</p>
                    <p><strong>Modelo: </strong> MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 967</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 1517</p>
                    <p><strong>Série: </strong> 312AZVN60015</p>
                    <p><strong>Modelo: </strong> LG 22MP410-B</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 968</p>
                    <p><strong>Série: </strong> ZF5248W28G</p>
                    <p><strong>IMEI 1: </strong> 359340983540177/05</p>
                    <p><strong>IMEI 2: </strong> 359340983540185/05</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/William%20Paulino%20Lima.png" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 William Paulino Lima</h3>
            <p><strong>Cargo: </strong> Gerente de Canais Digitais e Relacionamento</p>
            <p><strong>Departamento: </strong> Dir. Operações</p>
            <p><strong>E-mail: </strong> william.lima@amorsaude.com</p>
            <p><strong>CPF: </strong> 336.273.218-63</p>
            <p><strong>Centro de Custo: </strong> 22000 - Dir. Operações</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 994</p>
                    <p><strong>Série: </strong> zl73239kd6</p>
                    <p><strong>Modelo: </strong> Moto E13</p>
                </div>
            </div>
            <div class="attention-info">
                <h2>⚠️ Informações Importantes</h2>
                <p><strong>Alerta: </strong> O colaborador está utilizando os equipamentos, porém somente possuímos o
                    termo de responsabilidade assinado para o celular e sem dados dos demais.</p>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/Yago%20Mariano.png" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Yago dos Anjos Mariano</h3>
            <p><strong>Cargo: </strong> Consultor de Operações</p>
            <p><strong>Departamento: </strong> Consultoria</p>
            <p><strong>E-mail: </strong> yago.mariano@amorsaude.com</p>
            <p><strong>CPF: </strong> 120.120.005-42</p>
            <p><strong>Centro de Custo: </strong> 12006 - Regional 1</p>
            <img class="status" src="../../media/icon/off.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 1008</p>
                    <p><strong>Série: </strong> 2350AYO52C48</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1520</p>
                    <p><strong>Série: </strong> 2334CE2DA858</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1219</p>
                    <p><strong>Série: </strong> Não disponível</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🔌 Carregador</h4>
                    <p><strong>Patrimônio: </strong> 1222</p>
                    <p><strong>Série: </strong> SA18D53739</p>
                    <p><strong>Modelo: </strong> MC-107</p>
                </div>
                <div class="device">
                    <h4>🔌 Fonte</h4>
                    <p><strong>Patrimônio: </strong> 1220</p>
                    <p><strong>Série: </strong> SA11M40865</p>
                    <p><strong>Modelo: </strong> PA-1650-74</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 1521</p>
                    <p><strong>Série: </strong> PE9014206100</p>
                    <p><strong>Modelo: </strong> Lenovo V15 G3</p>
                </div>
                <div class="device">
                    <h4>📱 Smartphone</h4>
                    <p><strong>Patrimônio: </strong> 1221</p>
                    <p><strong>Série: </strong> SVNBD74575</p>
                    <p><strong>Modelo: </strong> Moto E22</p>
                    <p><strong>IMEI 1: </strong> 353689479970934</p>
                    <p><strong>IMEI 2: </strong> 353689479970942</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/Yago%20Oliveira.png" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Yago Romao Rocha Oliveira</h3>
            <p><strong>Cargo: </strong> Analista CS SAC Jr</p>
            <p><strong>Departamento: </strong> SAC</p>
            <p><strong>E-mail: </strong> yago.oliveira@amorsaude.com</p>
            <p><strong>CPF: </strong> 380.127.808-52</p>
            <p><strong>Centro de Custo: </strong> 12002 - SAC</p>
            <img class="status" src="../../media/icon/on.png" alt="">../../media/icon/on.png
            <div class="devices">
                <div class="device">
                    <h4>⌨️Teclado</h4>
                    <p><strong>Patrimônio (Teclado): </strong> 347</p>
                    <p><strong>Série (Teclado): </strong> 2234CE182209</p>
                    <p><strong>Patrimônio (Mouse): </strong> 348</p>
                </div>
                <div class="device">
                    <h4>🖱️ Mouse</h4>
                    <p><strong>Série (Mouse): </strong> 2230LZN0ZN78</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 332</p>
                    <p><strong>Série: </strong> Não disponível</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 195</p>
                    <p><strong>Série: </strong> PE0975GF</p>
                    <p><strong>Modelo: </strong> Lenovo V15 82ME</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 621</p>
                    <p><strong>Série: </strong> 273962288</p>
                    <p><strong>Modelo: </strong> Jabra HSC016</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 622</p>
                    <p><strong>Série: </strong> R9XT806p4sk</p>
                    <p><strong>Modelo: </strong> Galaxy A03</p>
                    <p><strong>IMEI 1: </strong> 350916873113230/01</p>
                    <p><strong>IMEI 2: </strong> 354654233113238/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
        <div class="card" onclick="this.classList.toggle('active')">
            <img src="../../media/collaborators/yasmin%20silva.jpg" alt="Foto do Colaborador" class="profile-pic">
            <h3>👤 Yasmin Campos da Silva</h3>
            <p><strong>Cargo: </strong> Assistente de Operações</p>
            <p><strong>Departamento: </strong> Backoffice</p>
            <p><strong>E-mail: </strong> Não informado</p>
            <p><strong>CPF: </strong> Não informado</p>
            <p><strong>Centro de Custo: </strong> 12005 - Backoffice</p>
            <img class="status" src="../../media/icon/on.png" alt="">
            <div class="devices">
                <div class="device">
                    <h4>💻 Notebook</h4>
                    <p><strong>Patrimônio: </strong> 105</p>
                    <p><strong>Série: </strong> PE08znfj</p>
                    <p><strong>Modelo: </strong> Lenovo IdeaPad 3 15ALC6</p>
                </div>
                <div class="device">
                    <h4>🎧 Fone</h4>
                    <p><strong>Patrimônio: </strong> 906</p>
                    <p><strong>Série: </strong> 23298GG15AB8</p>
                    <p><strong>Modelo: </strong> Logitech H390</p>
                </div>
                <div class="device">
                    <h4>⌨️🖱️ Teclado e Mouse</h4>
                    <p><strong>Patrimônio: </strong> 1456</p>
                    <p><strong>Série: </strong> 2232CE1C72B8</p>
                    <p><strong>Modelo: </strong> Logitech MK270</p>
                </div>
                <div class="device">
                    <h4>🔩 Suporte</h4>
                    <p><strong>Patrimônio: </strong> 1151</p>
                    <p><strong>Série: </strong> Não disponível</p>
                    <p><strong>Modelo: </strong> Alumínio</p>
                </div>
                <div class="device">
                    <h4>🖥️ Monitor</h4>
                    <p><strong>Patrimônio: </strong> 002</p>
                    <p><strong>Série: </strong> BR-00CPG1-TVB00-9CA-11Eu-A08</p>
                    <p><strong>Modelo: </strong> Dell P2018H</p>
                </div>
                <div class="device">
                    <h4>📱 Celular</h4>
                    <p><strong>Patrimônio: </strong> 510</p>
                    <p><strong>Série: </strong> r9qt402fxwy</p>
                    <p><strong>Modelo: </strong> Samsung A03</p>
                    <p><strong>IMEI 1: </strong> 351989271794777/01</p>
                    <p><strong>IMEI 2: </strong> 353297231794772/01</p>
                </div>
            </div>
            <div class="devices">
                <div class="responsibility-link">
                    <h4><a href="">📑 Termo de Responsabilidade</a></h4>
                </div>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>

</html>
