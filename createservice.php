<?php
    session_name("HATIDS");
    session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => "",
    'secure' => true,
    'httponly' => false,
    'samesite' => 'None'
    ]);
    session_start();
    date_default_timezone_set('America/Sao_Paulo');
    require('./connection.php');
    require('./functions.php');

    if (isset($_COOKIE['EMAIL']) && isset($_COOKIE['TYPE'])) {
        $email = $_COOKIE['EMAIL'];
        $type = $_COOKIE['TYPE'];
    }   
    else if (isset($_SESSION['EMAIL']) && isset($_SESSION['TYPE'])) {
        if (isset($_SESSION['LAST_ACTIVITY']) && time() - $_SESSION['LAST_ACTIVITY'] > 60 * 30) {
            expiredReturn();
        }
        $_SESSION['LAST_ACTIVITY'] = time();
        $email = $_SESSION['EMAIL'];
        $type = $_SESSION['TYPE'];
    } 
    else {
        expiredReturn();
    }

    if ($type != "CUSTOMER") {
        header("Location: /developermenu/");

        exit();
    }

    $rowuser = mysqli_fetch_assoc(searchEmailType($email, $type, $conn));
    $id = $rowuser["ID_CUSTOMER"];
    $sql = "SELECT * FROM TB_CATEGORY";
    $resultcat = mysqli_query($conn, $sql);

    if (isset($_POST["CREATE"])) {
        $title = filter_input(INPUT_POST, "title", FILTER_SANITIZE_STRING);
        $desc = filter_input(INPUT_POST, "description", FILTER_SANITIZE_STRING);
        $category = filter_input(INPUT_POST, "category", FILTER_SANITIZE_STRING);
        $status = 0;
        $iddev = null;

        $stmt = mysqli_prepare($conn, "INSERT INTO TB_SERVICES (COD_CUSTOMER, COD_DEVELOPER, COD_CATEGORY, TITLE, DESCRIPTION, STATUS) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssss", $id, $iddev, $category, $title, $desc, $status);
        $bool = mysqli_stmt_execute($stmt);

        if ($bool) {
            $_SESSION['servicemsg'] = "O serviço foi criado com sucesso!";
            $_SESSION['serviceclass'] = "is-success";
        } 
        else {
            $_SESSION['servicemsg'] = "Falha ao criar o serviço! Por favor, tente novamente mais tarde!";
            $_SESSION['serviceclass'] = "is-danger";
        }
        header("Location: /customermenu/");
        unset($_POST);
    }
    mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hatchfy</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com/css2?family=Baloo+2&family=Roboto&display=swap">
    <script src="/js/bulma-toast.min.js"></script>
    <script src="/js/vue.js"></script>
    <script src="/js/jscript.js"></script>
    <script src="/js/v-mask.min.js"></script>
    <script src="/js/moment.js"></script>
</head>

<body class="background">
    <div id="app" class="script">
        <?php require("./headercustomer.php"); ?>
        <br>
        <section class="hero is-fullheight">
            <div class="hero-body">
                <div class="container">
                    <section class="hero is-dark">
                        <div class="hero-body is-dark">
                            <p class="title">
                                Crie seu Serviço
                            </p>
                        </div>
                    </section>
                    <form action="" method="POST">
                        <div class="section">
                            <div class="columns is-centered">
                                <div class="column is-5">
                                    <div class="field">
                                        <label class="label">Título do serviço</label>
                                        <input class="input" type="text" v-model="title" name="title" placeholder="Digite o título do serviço">
                                    </div>
                                    <div class="control">
                                        <label class="label" for="description">Descrição do serviço</label>
                                        <textarea v-model="description" class="textarea has-fixed-size" placeholder="Digite a descrição do serviço" name="description"></textarea>
                                    </div>
                                </div>
                                <div class="column is-5">
                                    <div class="field">
                                        <label class="label" for="category">Tipo de serviço</label>
                                        <div class="select">
                                            <select name="category" v-model="category">
                                                <option value="" disabled selected>Selecione o tipo do serviço</option>
                                                <?php while ($rowcat = mysqli_fetch_assoc($resultcat)) { ?><option value="<?php echo $rowcat['ID_CATEGORY']; ?>"><?php echo $rowcat['NAME']; ?></option> <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="field is-grouped is-grouped-centered is-grouped-multiline">
                                <p class="control">
                                    <button type="submit" name="CREATE" class="button is-primary">Criar</button>
                                </p>
                                <p class="control">
                                    <button type="button" class="button is-danger" onclick="window.location.replace('/customermenu/')">Cancelar criação</button>
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php require "baseboard.php"?>
        </section>
    </div>
    <noscript> <style> .script { display: none; } </style> <section class="hero is-fullheight"> <div class="hero-body"> <div class="container has-text-centered"> <div class="box has-text-centered"> <p class="title font-face"> JavaScript não habilitado! </p> <br> <p class="title is-5"> Por favor, habilite o JavaScript para a página funcionar! </p> </div> </div> </div> </section> </noscript>
    <script>
        var vue = new Vue({
            el: '#app',
            data: {
                isActiveBurger: false,
                title: "",
                description: "",
                category: "",
            },
            methods: {
                onClickBurger() {
                    this.isActiveBurger = !this.isActiveBurger
                },
                onClickLogout() {
                    window.location.replace("/logout/")
                },
                showMessage(msg, msgclass, position) {
                    bulmaToast.toast({
                        message: msg,
                        type: msgclass,
                        duration: 6000,
                        position: position,
                        dismissible: true,
                        pauseOnHover: true,
                        closeOnClick: false,
                        animate: {
                            in: 'fadeIn',
                            out: 'fadeOut'
                        },
                    });
                }
            }
        })
    </script>
    <script>
        const form = document.querySelector("#app .hero .hero-body .container form");
        form.addEventListener("submit", function(event) {
            if (vue.$data.title == "") {
                event.preventDefault();
                vue.showMessage("Por favor, preencha o título do serviço!", "is-danger", "top-center");
            } 
            else if (vue.$data.description == "") {
                event.preventDefault();
                vue.showMessage("Por favor, preencha a descrição do serviço!", "is-danger", "top-center");
            } 
            else if (vue.$data.category == "") {
                event.preventDefault();
                vue.showMessage("Por favor, preencha a categoria do serviço!", "is-danger", "top-center");
            }
        });
    </script>
</body>
</html>