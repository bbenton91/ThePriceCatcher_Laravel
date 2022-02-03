<script>
    window.onload = () => {
        var dropdown = document.querySelector('.dropdown');
        dropdown.addEventListener('click', (evt) => {
            evt.stopPropagation();
            dropdown.classList.toggle('is-active');
        })
    };

</script>

<div id="dropdownDiv">
    <div class="dropdown is-right">

        <div class="dropdown-trigger">

        <span><img src="https://img.icons8.com/ios/50/000000/menu.png"/></span>


        </div>

        <div class="dropdown-menu" id="dropdown-menu" role="menu">

        <div class="dropdown-content">

            <a href={{$prepend."/"}} class="dropdown-item">
                Home
            </a>

            <hr class="dropdown-divider">

            <a href={{$prepend."/browse"}} class="dropdown-item">
                Browse
            </a>

            <hr class="dropdown-divider">

            <a href="#" class="dropdown-item">
                About
            </a>

        </div>

        </div>
    </div>
</div>
