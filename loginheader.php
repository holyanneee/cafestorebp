
<header>
    <h1>My Website</h1>
    <button id="showLogin">Login / Sign Up</button>
</header>

<style>
    header {
        background: #512da8;
        color: white;
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    button#showLogin {
        background: white;
        color: #512da8;
        border: none;
        padding: 10px 20px;
        cursor: pointer;
        font-weight: bold;
        border-radius: 5px;
    }

    .popup-container {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 20px;
        box-shadow: 0px 5px 15px rgba(0,0,0,0.3);
        display: none;
        border-radius: 10px;
    }

    .close-popup {
        background: red;
        color: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        margin-top: 10px;
        border-radius: 5px;
    }
</style>

<div id="popup" class="popup-container">
    <button class="close-popup" onclick="hidePopup()">Close</button>
    <?php include 'login.php'; ?>
</div>

<script>
    document.getElementById('showLogin').addEventListener('click', function () {
        document.getElementById('popup').style.display = 'block';
    });

    function hidePopup() {
        document.getElementById('popup').style.display = 'none';
    }
</script>
