<style>
    :root {
        --km-color: #003629;
        --km-overlay: rgba(0, 54, 41, 0.48);
        --km-lighter: #0e4133;

        --anb-color: #9EC6F3;
        --anb-overlay: rgba(158, 198, 243, 0.48);
        --anb-lighter: #89B4E9;
    }


    .page-bg {
        position: relative;

        background: url(images/header.jpg) no-repeat;
        background-size: cover;
        background-position: center;
        height: 100%;
        filter: brightness(0.8) contrast(0.9);

    }

    .page-bg::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color:
            <?= $_SESSION['store_code'] === 'KM' ? 'var(--km-overlay)' : 'var(--anb-overlay)' ?>
        ;

        z-index: 0;
    }

    .page-bg>* {
        position: relative;
        z-index: 1;
    }

    .nav a {
        position: relative;
        display: inline-block;
        padding-bottom: 4px;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .nav a::before {
        content: "";
        position: absolute;
        left: 0;
        bottom: 0;
        width: 100%;
        height: 2px;
        background-color: transparent;
        transition: background-color 0.3s ease, width 0.3s ease;
    }

    .nav a.active::before,
    .nav a:hover::before {
        background-color:
            <?= $_SESSION['store_code'] === 'KM' ? 'var(--km-lighter)' : 'var(--anb-lighter)' ?>
        ;
    }

    .text-color, .text-hover-color:hover {
        color:
            <?= $_SESSION['store_code'] === 'KM' ? 'var(--km-color)' : 'var(--anb-color)' ?>
        ;
    }

    .bg-color {
        background-color:
            <?= $_SESSION['store_code'] === 'KM' ? 'var(--km-color)' : 'var(--anb-color)' ?>
        ;
    }

    .border-color {
        border-color:
            <?= $_SESSION['store_code'] === 'KM' ? 'var(--km-color)' : 'var(--anb-color)' ?>
        ;
    }

    .bg-color {
        background-color:
            <?= $_SESSION['store_code'] === 'KM' ? 'var(--km-color)' : 'var(--anb-color)' ?>
        ;
    }

    .bg-lighter-color, .bg-hover-color:hover {
        background-color:
            <?= $_SESSION['store_code'] === 'KM' ? 'var(--km-lighter)' : 'var(--anb-lighter)' ?>
        ;
    }
</style>