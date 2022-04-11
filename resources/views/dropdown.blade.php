<script>
    window.onload = () => {
        var dropdown = document.querySelector('.dropdown');
        dropdown.addEventListener('click', (evt) => {
            evt.stopPropagation();
            dropdown.classList.toggle('is-active');
        })
    };

</script>

<div id="dropdownDiv" class="w-full flex justify-end mr-4">
    <div class="dropdown h-full flex">

        <div class="dropdown-trigger self-center">
            <span><img src="https://img.icons8.com/ios/50/000000/menu.png" class="h-20 w-20"/></span>
        </div>

        <div class="dropdown-menu" id="dropdown-menu" role="menu">

        <div class="dropdown-content">

            <a href={{$prepend."/"}} class="dropdown-item text-4xl">
                Home
            </a>

            <hr class="dropdown-divider">

            <a href={{$prepend."/browse"}} class="dropdown-item text-4xl">
                Browse
            </a>

            <hr class="dropdown-divider">

            <a href="#" class="dropdown-item text-4xl">
                About
            </a>

        </div>

        </div>
    </div>
</div>
