<?php
include "util.php";

// parse a .CSV line containing commas and quoted strings
function parse_csv($line) {
    $accumulator = "";
    $i = 0;
    foreach (explode("\"", $line) as $column) {
        if ($i % 2 === 0) {
            $accumulator .= str_replace(",", ";", $column);
        } else {
            $accumulator .= $column;
        }

        $i += 1;
    }

    $columns = explode(";", $accumulator);
    return $columns;
}

// parse a semicolon-separated line without quotes
// this function also trims whitespace in columns, but it's not neccessary since html ignores whitespace anyways
function parse_scsv($line) {
    $columns = explode(";", $line);
    $trimmer = function ($column) {
        return trim($column);
    };
    return array_map($trimmer, $columns);
}

class Row {
    public $is_bold;
    public $cells;

    public function __construct($is_bold, $cells) {
        $this->is_bold = $is_bold;
        $this->cells = $cells;
    }
}

function read_table_from_file($filename) {
    $filetext = file_get_contents($filename);
    if ($filetext === false) {
        return false;
    }

    // Skip UTF-8 BOM (real)
    if (strlen($filetext) >= 3 &&
        ord($filetext[0]) === 0xEF &&
        ord($filetext[1]) === 0xBB &&
        ord($filetext[2]) === 0xBF) {
        $filetext = substr($filetext, 3);
    }

    $table = array();
    foreach (explode("\n", $filetext) as $line) {
        if (strlen(trim($line)) === 0) {
            // Empty row, skip
            continue;
        }

        $is_bold = $line[0] == ">";
        if ($is_bold) {
            // Skip '>'
            $line = substr($line, 1);
        }

        $cells = parse_scsv($line);

        // push empty strings until array has size 4
        $n = 4 - count($cells);
        for ($i = 0; $i < $n; $i++) {
            array_push($cells, "");
        }

        array_push($table, new Row($is_bold, $cells));
    }
    return $table;
}

// now that i think about it, why didn't i use flex-wrap: wrap?
function get_dish_table() {
    $text = "";

    <<<END
    <div class="row">
        <div class="col-md-2"></div>
        <div class="col-12 col-md-4">
            <table class="table">
                <thead>
                <tr>
                    <th colspan="2">Тапас</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Тапас с паштетом из куры и грецким орехом</td>
                    <td>110</td>
                </tr>
                <tr>
                    <td>Тапас с моцареллой, соусом песто и лососем</td>
                    <td>140</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="col-12 col-md-4">
            <table class="table">
                <thead>
                <tr>
                    <th colspan="2">Тапас</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Тапас с паштетом из куры и грецким орехом</td>
                    <td>110</td>
                </tr>
                <tr>
                    <td>Тапас с моцареллой, соусом песто и лососем</td>
                    <td>140</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="col-md-2"></div>
    </div>
    END;

    $table = read_table_from_file("./dish.txt");
    // print many rows
    for ($i = 0; $i < count($table);) {
        // print row header
        $text .= <<<END
        <div class="row">
        <div class="col-md-2"></div>
        END;

        // print two html tables before going to the next row
        for ($j = 0; $j < 2; $j++) {
            // print table header
            $text .= <<<END
            <div class="col-12 col-md-4">
            <table class="table">
            END;

            for ($k = 0; $i < count($table); $k++) {
                if ($i < count($table)) {
                    $row = $table[$i];
                    // $i is not incremented here
                }

                if ($row->is_bold) {
                    if ($k === 0) {
                        // bold row in first table row
                        $i++;
                        $text .= <<<END
                        <thead>
                        <tr>
                            <th colspan="2">{$row->cells[0]}</th>
                        </tr>
                        </thead>
                        END;
                    } else {
                        // another bold row. let the next iteration of $j loop handle it
                        break;
                    }
                } else {
                    $i++;
                    $text .= "
                    <tr>
                        <td>{$row->cells[0]}</td>
                        <td>{$row->cells[1]}</td>
                    </tr>";
                }
            }

            // print table footer
            $text .= "
            </table>
            </div>";
        }

        // print row footer
        $text .= <<<END
        <div class="col-md-2"></div>
        </div>
        END;
    }

    return $text;
}

function get_wine_table() {
    $text = "";
    foreach (read_table_from_file("./wine.txt") as $row) {
        if (!$row->is_bold) {
            $text .= "
            <tr>
                <td>{$row->cells[0]}<br>
                    <small>{$row->cells[1]}</small>
                </td>
                <td>{$row->cells[2]}</td>
                <td>{$row->cells[3]}</td>
            </tr>";
        } else {
            $text .= <<<END
            <thead>
            <tr>
                <th colspan="1">{$row->cells[0]}</th>
                <th colspan="1">{$row->cells[1]}</th>
                <th colspan="1">{$row->cells[2]}</th>
            </tr>
            </thead>
            END;
        }
    }

    return $text;
}
?>

<!doctype html>
<html lang="en" data-collapsed="true">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Preload first-screen-carousel imgs -->
    <link rel="preload" href="imgs/first-screen-carousel/1.jpg" as="image">
    <link rel="preload" href="imgs/first-screen-carousel/2.jpg" as="image">
    <link rel="preload" href="imgs/first-screen-carousel/3.jpg" as="image">
    <link rel="preload" href="imgs/first-screen-carousel/mob1.jpg" as="image">
    <link rel="preload" href="imgs/first-screen-carousel/mob2.jpg" as="image">
    <link rel="preload" href="imgs/first-screen-carousel/mob3.jpg" as="image">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">

    <!-- Favicon -->
    <link rel="icon" href="favicon.ico" type="image/x-icon"/>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <!-- <link rel="stylesheet" href="bootstrap/bootstrap.min.css"> -->

    <!-- Mobirise Icons -->
    <link rel="stylesheet" href="lib/mobirise/style.css">

    <!-- My styles CSS -->
    <link rel="stylesheet" href="mystyles.css">

    <!-- Font: Rubik Light 300 -->
    <link
            href="https://fonts.googleapis.com/css?family=Rubik:300,400,500,600,700,800,900,300i,400i,500i,600i,700i,800i,900i&display=swap"
            rel="stylesheet">

    <title>Bar 3/4</title>
</head>

<body>
<nav id="my-navbar" class="navbar navbar-expand-md navbar-light mb-3 my-transparent-if-top my-fix-on-top">
    <div class="container-fluid">
        <a href="#" class="navbar-brand mr-3"><img id="logo" src="imgs/DailyBar34.png" alt="Logo"></a>
        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse"
                onclick="document.documentElement.dataset.collapsed = document.documentElement.dataset.collapsed === 'false' ? 'true' : 'false'; my.updateIsNavTransparent();">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <!--<div class="navbar-nav">
                <a href="#" class="nav-item nav-link">Register</a>
                <a href="#" class="nav-item nav-link">Login</a>
                <a href="#" class="nav-item nav-link active">Home</a>
                <a href="#" class="nav-item nav-link">Services</a>
                <a href="#" class="nav-item nav-link">About</a>
                <a href="#" class="nav-item nav-link">Contact</a>
            </div>-->
            <div class="navbar-nav ml-auto">
                <a href="https://www.instagram.com/daily34bar/" target="_blank" class="nav-item nav-link">
                    <img id="instagram-icon" src="imgs/inst_inverted.png" alt="Instagram logo" width="20px">
                    Instagram</a>
                <a onclick="my.scrollToElement(document.getElementById('dish-menu-start'));"
                   class="nav-item nav-link">Меню</a>
                <a onclick="my.scrollToElement(document.getElementById('contacts'));"
                   class="nav-item nav-link">Контакты</a>
                <a onclick="my.scrollToElement(document.getElementById('booking'));"
                   class="nav-item nav-link">Бронирование</a>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid px-0">
    <div id="first-screen-carousel" class="d-none d-md-block carousel slide" data-interval="false">
        <!-- Carousel indicators -->
        <ol class="carousel-indicators">
            <li data-target="#first-screen-carousel" data-slide-to="0" class="active"></li>
            <li data-target="#first-screen-carousel" data-slide-to="1"></li>
            <li data-target="#first-screen-carousel" data-slide-to="2"></li>
            <li data-target="#first-screen-carousel" data-slide-to="3"></li>
        </ol>
        <!-- Wrapper for carousel items -->
        <div class="carousel-inner">
            <div class="my-one-screen carousel-item active">
                <div class="container-fluid my-one-screen-item"
                     style="background-image: url('imgs/first-screen-carousel/1.jpg');">
                    <div class="row my-one-screen-item">
                        <div class="col align-self-center">
                            <h1>Бар 3/4</h1>
                            <h3>Wine & Kitchen</h3>
                            <p>
                                Новый Бар в историческом центре
                                с авторской, европейской кухней,
                                вином и коктейлями
                            </p>
                            <br>
                            <!-- <a href="#dish-gallery" class="my-button">МЕНЮ</a> -->
                            <a onclick="my.scrollToElement(document.getElementById('dish-menu-start'));"
                               class="my-button">МЕНЮ</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="my-one-screen carousel-item">
                <div class="container-fluid my-one-screen-item"
                     style="background-image: url('imgs/first-screen-carousel/2.jpg');">
                    <div class="row my-one-screen-item">
                        <div class="col align-self-center">
                            <h1>Широкий выбор вин</h1>
                            <!-- <p>
                                    А
                                </p> -->
                            <br>
                            <!-- <a href="#wine-menu" class="my-button">ВИНО</a> -->
                            <a onclick="my.scrollToElement(document.getElementById('wine-menu-start'));"
                               class="my-button">ВИНО</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="my-one-screen carousel-item">
                <div class="container-fluid my-one-screen-item"
                     style="background-image: url('imgs/first-screen-carousel/3.jpg'); background-position: bottom;">
                    <div class="row my-one-screen-item">
                        <div class="col align-self-center">
                            <h1>Уютный интерьер</h1>
                            <!-- <p>
                                    Вставить текст
                                </p> -->
                            <br>
                            <!-- <a href="#interior-gallery" class="my-button">ГАЛЕРЕЯ</a> -->
                            <a onclick="my.scrollToElement(document.getElementById('interior-gallery-start'));"
                               class="my-button">ГАЛЕРЕЯ</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="my-one-screen carousel-item">
                <div class="container-fluid my-one-screen-item"
                     style="background-image: url('imgs/first-screen-carousel/4.jpg');">
                    <div class="row my-one-screen-item">
                        <div class="col align-self-center">
                            <h1>Банкеты</h1>
                            <br>
                            <!-- <a href="#contacts" class="my-button">ПОДРОБНЕЕ</a> -->
                            <a onclick="my.scrollToElement(document.getElementById('contacts'));"
                               class="my-button">ПОДРОБНЕЕ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Carousel controls -->
        <a class="carousel-control-prev" href="#first-screen-carousel" data-slide="prev">
            <span class="d-none d-md-inline mbri-left my-arrow-button"></span>
        </a>
        <a class="carousel-control-next" href="#first-screen-carousel" data-slide="next">
            <span class="d-none d-md-inline mbri-right my-arrow-button"></span>
        </a>

        <!-- Mobile Carousel controls -->
        <a class="carousel-control-prev" href="#first-screen-carousel" data-slide="prev" style="width: 110px;">
            <div class="align-self-end" style="padding-bottom: 50px;">
                <span class="d-inline d-md-none mbri-left my-arrow-button"></span>
            </div>
        </a>
        <a class="carousel-control-next" href="#first-screen-carousel" data-slide="next" style="width: 110px;">
            <div class="align-self-end" style="padding-bottom: 50px;">
                <span class="d-inline d-md-none mbri-right my-arrow-button"></span>
            </div>
        </a>
    </div>
    <div id="first-screen-carousel-mob" class="d-block d-md-none carousel slide" data-interval="false">
        <!-- Carousel indicators -->
        <ol class="carousel-indicators">
            <li data-target="#first-screen-carousel-mob" data-slide-to="0" class="active"></li>
            <li data-target="#first-screen-carousel-mob" data-slide-to="1"></li>
            <li data-target="#first-screen-carousel-mob" data-slide-to="2"></li>
            <li data-target="#first-screen-carousel-mob" data-slide-to="3"></li>
        </ol>
        <!-- Wrapper for carousel items -->
        <div class="carousel-inner">
            <div class="my-one-screen carousel-item active">
                <div class="container-fluid my-one-screen-item"
                     style="background-image: url('imgs/first-screen-carousel/mob1.jpg');">
                    <div class="row my-one-screen-item">
                        <div class="col align-self-center">
                            <h1>Бар 3/4</h1>
                            <h3>Wine & Kitchen</h3>
                            <p>
                                Новый Бар в историческом центре
                                с авторской, европейской кухней,
                                вином и коктейлями
                            </p>
                            <br>
                            <!-- <a href="#dish-gallery" class="my-button">МЕНЮ</a> -->
                            <a onclick="my.scrollToElement(document.getElementById('dish-menu-start'));"
                               class="my-button">МЕНЮ</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="my-one-screen carousel-item">
                <div class="container-fluid my-one-screen-item"
                     style="background-image: url('imgs/first-screen-carousel/mob2.jpg');">
                    <div class="row my-one-screen-item">
                        <div class="col align-self-center">
                            <h1>Широкий выбор вин</h1>
                            <!-- <p>
                                    А
                                </p> -->
                            <br>
                            <!-- <a href="#wine-menu" class="my-button">ВИНО</a> -->
                            <a onclick="my.scrollToElement(document.getElementById('wine-menu-start'));"
                               class="my-button">ВИНО</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="my-one-screen carousel-item">
                <div class="container-fluid my-one-screen-item"
                     style="background-image: url('imgs/first-screen-carousel/mob3.jpg'); background-position: bottom;">
                    <div class="row my-one-screen-item">
                        <div class="col align-self-center">
                            <h1>Уютный интерьер</h1>
                            <!-- <p>
                                    Вставить текст
                                </p> -->
                            <br>
                            <!-- <a href="#interior-gallery" class="my-button">ГАЛЕРЕЯ</a> -->
                            <a onclick="my.scrollToElement(document.getElementById('interior-gallery-start'));"
                               class="my-button">ГАЛЕРЕЯ</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="my-one-screen carousel-item">
                <div class="container-fluid my-one-screen-item"
                     style="background-image: url('imgs/first-screen-carousel/mob4.jpg'); background-position: bottom;">
                    <div class="row my-one-screen-item">
                        <div class="col align-self-center">
                            <h1>Банкеты</h1>
                            <br>
                            <!-- <a href="#contacts" class="my-button">ПОДРОБНЕЕ</a> -->
                            <a onclick="my.scrollToElement(document.getElementById('contacts'));"
                               class="my-button">ПОДРОБНЕЕ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Carousel controls -->
        <a class="carousel-control-prev" href="#first-screen-carousel-mob" data-slide="prev">
            <span class="d-none d-md-inline mbri-left my-arrow-button"></span>
        </a>
        <a class="carousel-control-next" href="#first-screen-carousel-mob" data-slide="next">
            <span class="d-none d-md-inline mbri-right my-arrow-button"></span>
        </a>

        <!-- Mobile Carousel controls -->
        <a class="carousel-control-prev" href="#first-screen-carousel-mob" data-slide="prev" style="width: 110px;">
            <div class="align-self-end" style="padding-bottom: 50px;">
                <span class="d-inline d-md-none mbri-left my-arrow-button"></span>
            </div>
        </a>
        <a class="carousel-control-next" href="#first-screen-carousel-mob" data-slide="next" style="width: 110px;">
            <div class="align-self-end" style="padding-bottom: 50px;">
                <span class="d-inline d-md-none mbri-right my-arrow-button"></span>
            </div>
        </a>
    </div>
</div>

<div class="container-fluid">
    <!--
    <div id="dish-gallery" class="my-page-row my-page-row-bright">
        <div style="padding-bottom: 20px;" class="row justify-content-center">
            <div class="card-deck">
                <div class="card my-food-menu-card">
                    <img src="imgs/menu/1.jpg" class="card-img-top" alt="...">
                    <div class="card-body text-center">
                        <p class="card-text">Салат с тыквой, мягким сыром и кедровыми орешками</p>
                        <h3>260₽</h3>
                    </div>
                </div>
                <div class="card my-food-menu-card">
                    <img src="imgs/menu/2.jpg" class="card-img-top" alt="...">
                    <div class="card-body text-center">
                        <p class="card-text">Пене с индейкой, стручковой фасолью и томатами черри</p>
                        <h3>360₽</h3>
                    </div>
                </div>
                <div class="card my-food-menu-card">
                    <img src="imgs/menu/3.jpg" class="card-img-top" alt="...">
                    <div class="card-body text-center">
                        <p class="card-text">Тартар из говядины с оливками, вяленными томатами и рукколой</p>
                        <h3>420₽</h3>
                    </div>
                </div>
            </div>
        </div>
        <div style="padding-top: 0;" class="row justify-content-center">
            <div class="card-deck">
                <div class="card my-food-menu-card">
                    <img src="imgs/menu/4.jpg" class="card-img-top" alt="...">
                    <div class="card-body text-center">
                        <p class="card-text">Куриная грудка с полентой и грибным соусом</p>
                        <h3>360₽</h3>
                    </div>
                </div>
                <div class="card my-food-menu-card">
                    <img src="imgs/menu/5.jpg" class="card-img-top" alt="...">
                    <div class="card-body text-center">
                        <p class="card-text">Гороховый крем-суп с беконом и сливками</p>
                        <h3>230₽</h3>
                    </div>
                </div>
                <div class="card my-food-menu-card">
                    <img src="imgs/menu/6.jpg" class="card-img-top" alt="...">
                    <div class="card-body text-center">
                        <p class="card-text">Панна-Котта с топпингом из красного вина</p>
                        <h3>220₽</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    -->

    <hr id="dish-menu-start">

    <!-- Dish Menu table -->
    <div class="my-page-row my-page-row-bright">
        <h1 style="text-align: center; margin-bottom: 25px;">Меню</h1>
        <?php
        init_util();

        echo get_dish_table();
        ?>
    </div>

    <hr id="wine-menu-start">

    <!-- Wine table -->
    <div class="my-page-row my-page-row-bright">
        <h1 style="text-align: center; margin-bottom: 25px;">Винная карта</h1>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-12 col-md-6">
                <table class="table">
                    <?php
                    echo get_wine_table();
                    ?>
                </table>
            </div>
            <div class="col-md-3"></div>
        </div>
    </div>

    <hr id="booking">

    <div class="my-page-row my-page-row-bright">
        <div class="row justify-content-center">
            <div class="col-auto">
                <h1>ЗАКАЗ СТОЛИКА</h1>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-auto">
                <p class="secondary-text my-0" style="font-size: 1.5rem">Вы можете заполнить форму ниже или
                    позвонить</p>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-auto">
                <p class="secondary-text" style="font-size: 1.5rem">нам по телефону +7 911 930-34-34</p>
            </div>
        </div>

        <br>

        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-12 col-md-6">
                <div class="jumbotron">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="inputTime" id="booking-today-label">Сегодня</label>
                                    <input type="time" autocomplete="off" required="required" class="form-control"
                                           id="inputTime">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        На 1 человека
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" onclick="my.setNumberOfPeople(1, 'На 1 человека')">На 1 человека</a>
                                        <a class="dropdown-item" onclick="my.setNumberOfPeople(2, 'На 2 человека')">На 2 человека</a>
                                        <a class="dropdown-item" onclick="my.setNumberOfPeople(3, 'На 3 человека')">На 3 человека</a>
                                        <a class="dropdown-item" onclick="my.setNumberOfPeople(4, 'На 4 человека')">На 4 человека</a>
                                        <a class="dropdown-item" onclick="my.setNumberOfPeople(5, 'На 5 человек')">На 5 человек</a>
                                        <a class="dropdown-item" onclick="my.setNumberOfPeople(6, 'На 6 человек')">На 6 человек</a>
                                        <a class="dropdown-item" onclick="my.setNumberOfPeople(7, 'На 7 человек')">На 7 человек</a>
                                        <a class="dropdown-item" onclick="my.setNumberOfPeople(8, 'На 8 человек')">На 8 человек</a>
                                        <a class="dropdown-item" onclick="my.setNumberOfPeople(9, 'На 9 человек')">На 9 человек</a>
                                        <a class="dropdown-item" onclick="my.setNumberOfPeople(10, 'На 10 человек')">На 10 человек</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label for="inputName">Имя</label>
                                    <input type="text" autocomplete="off" required="required" placeholder="Имя"
                                           class="form-control"
                                           id="inputName">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label for="inputPhone">Телефон</label>
                                    <input type="tel" required="required" placeholder="+7 ___ ___-__-__"
                                           class="form-control"
                                           id="inputPhone">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-md-12">
                                <div class="form-group">
                        <textarea autocomplete="off"
                                  required="required"
                                  placeholder="Пожелания (необязательно, будут учтены по возможности)"
                                  class="form-control" id="inputMessage"></textarea>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row justify-content-center">
                            <div class="col-auto">
                                <button class="my-button2" onclick="my.book()">ЗАБРОНИРОВАТЬ</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="alert">
                    <!-- Notification on successfull booking goes here -->
                </div>
            </div>
            <div class="col-md-3"></div>
        </div>

        <!--        <form action="message.php" method="post">-->
        <!--
        <div class="jumbotron">
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label for="exampleInputTime1" id="booking-today-label">Сегодня</label>
                        <input type="time" autocomplete="off" required="required" class="form-control"
                               id="exampleInputTime1">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <select id="exampleInputNumber1">
                        <option value="1">на 1 человека</option>
                        <option value="2">на 2 человека</option>
                        <option value="3">на 3 человека</option>
                        <option value="4">на 4 человека</option>
                        <option value="5">на 5 человек</option>
                        <option value="6">на 6 человек</option>
                        <option value="7">на 7 человек</option>
                        <option value="8">на 8 человек</option>
                        <option value="9">на 9 человек</option>
                        <option value="10">на 10 человек</option>
                    </select>
                </div>
                <div class="col-md-3"></div>
            </div>
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label for="exampleInputName1">Имя</label>
                        <input type="text" autocomplete="off" required="required" placeholder="Имя"
                               class="form-control"
                               id="exampleInputName1">
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="form-group">
                        <label for="exampleInputPhone1">Телефон</label>
                        <input type="tel" required="required" placeholder="+7 ___ ___-__-__" class="form-control"
                               id="exampleInputPhone1">
                    </div>
                </div>
                <div class="col-md-3"></div>
            </div>
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <textarea autocomplete="off"
                                  required="required"
                                  placeholder="Пожелания (необязательно, будут учтены по возможности)"
                                  class="form-control" id="exampleInputMessage1"></textarea>
                    </div>
                </div>
                <div class="col-md-3"></div>
            </div>
            <br>
            <div class="row justify-content-center">
                <div class="col-auto">
                    <button class="my-button2" onclick="my.book()">ЗАБРОНИРОВАТЬ</button>
                </div>
            </div>
        </div>
        -->
        <!--        </form>-->
    </div>

    <hr id="interior-gallery-start" class="my-0">

    <div id="gallery" class="row my-page-row my-page-row-dark">
        <div class="col-md-2"></div>
        <div class="col-12 col-md-8 px-0">
            <div id="gallery-carousel" class="carousel slide" data-interval="false">
                <!-- Carousel indicators -->
                <ol class="carousel-indicators">
                    <li data-target="#gallery-carousel" data-slide-to="0" class="active"></li>
                    <li data-target="#gallery-carousel" data-slide-to="1"></li>
                    <li data-target="#gallery-carousel" data-slide-to="2"></li>
                    <li data-target="#gallery-carousel" data-slide-to="3"></li>
                    <li data-target="#gallery-carousel" data-slide-to="4"></li>
                    <li data-target="#gallery-carousel" data-slide-to="5"></li>
                </ol>
                <!-- Wrapper for carousel items -->
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="imgs/gallery/1.jpg" width="100%">
                    </div>
                    <div class="carousel-item">
                        <img src="imgs/gallery/2.jpg" width="100%">
                    </div>
                    <div class="carousel-item">
                        <img src="imgs/gallery/3.jpg" width="100%">
                    </div>
                    <div class="carousel-item">
                        <img src="imgs/gallery/4.jpg" width="100%">
                    </div>
                    <div class="carousel-item">
                        <img src="imgs/gallery/5.jpg" width="100%">
                    </div>
                    <div class="carousel-item">
                        <img src="imgs/gallery/6.jpg" width="100%">
                    </div>
                </div>
                <!-- Carousel controls -->
                <a class="carousel-control-prev" href="#gallery-carousel" data-slide="prev">
                    <span class="d-none d-md-inline mbri-left my-arrow-button"></span>
                </a>
                <a class="carousel-control-next" href="#gallery-carousel" data-slide="next">
                    <span class="d-none d-md-inline mbri-right my-arrow-button"></span>
                </a>
            </div>
        </div>
        <div class="col-md-2"></div>
    </div>

    <div id="contacts" class="row my-page-row my-page-row-very-dark">
        <!-- Left -->
        <div class="col-md-2"></div>
        <div class="col-12 col-md-4">
            <h2 onclick="/*my.showVisitorCounter();*/ window.location.href = '/manager.html';">КОНТАКТЫ</h2>
            <p id="hidden-counter" style="height: 0; overflow: hidden;">
                This text will be replaced with visitor count
            </p>
            <h5>Адрес:</h5>
            <p>
                ул. Чайковского 34, Санкт-Петербург
            </p>
            <h5>
                Телефон:
            </h5>
            <p>
                +7 911 930-34-34
            </p>
            <h5>
                E-mail:
            </h5>
            <p>
                mail@34.spb.ru
            </p>
        </div>
        <!-- Right -->
        <div class="col-12 col-md-4">
            <div class="gmap_canvas">
                <iframe width="100%" height="500"
                        src="https://maps.google.com/maps?q=%D1%83%D0%BB%D0%B8%D1%86%D0%B0%20%D0%A7%D0%B0%D0%B9%D0%BA%D0%BE%D0%B2%D1%81%D0%BA%D0%BE%D0%B3%D0%BE,%2034,%20%D0%A1%D0%B0%D0%BD%D0%BA%D1%82-%D0%9F%D0%B5%D1%82%D0%B5%D1%80%D0%B1%D1%83%D1%80%D0%B3&t=&z=13&ie=UTF8&iwloc=&output=embed"
                        frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>
            </div>
        </div>
        <div class="col-md-2"></div>
    </div>

    <!--
    <hr>
    <footer>
        <div class="row">
            <div class="col-md-6">
                <p>Copyright &copy; 2019 Tutorial Republic</p>
            </div>
            <div class="col-md-6 text-md-right">
                <a href="#" class="text-dark">Terms of Use</a>
                <span class="text-muted mx-2">|</span>
                <a href="#" class="text-dark">Privacy Policy</a>
            </div>
        </div>
    </footer>
    -->
</div>

<!-- JQuery, Popper, Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"
        integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js"
        integrity="sha384-w1Q4orYjBQndcko6MimVbzY0tgp4pWB4lZ7lr30WKz0vr/aWKhXdBNmNb5D92v7s"
        crossorigin="anonymous"></script>

<!--
<script src="bootstrap/jquery-3.5.1.slim.min.js"></script>
<script src="bootstrap/popper.min.js"></script>
<script src="bootstrap/bootstrap.min.js"></script>
-->

<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function (m, e, t, r, i, k, a) {
        m[i] = m[i] || function () {
            (m[i].a = m[i].a || []).push(arguments)
        };
        m[i].l = 1 * new Date();
        k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
    })
    (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

    ym(71736766, "init", {
        clickmap: true,
        trackLinks: true,
        accurateTrackBounce: true
    });
</script>
<noscript>
    <div><img src="https://mc.yandex.ru/watch/71736766" style="position:absolute; left:-9999px;" alt=""/></div>
</noscript>
<!-- /Yandex.Metrika counter -->

<script type="module" src="main.js"></script>
</body>

</html>