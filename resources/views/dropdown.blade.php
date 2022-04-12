{{-- <script>
    window.onload = () => {
        var dropdown = document.querySelector('.dropdown');
        dropdown.addEventListener('click', (evt) => {
            evt.stopPropagation();
            dropdown.classList.toggle('is-active');
        })
    };

</script> --}}

<div id="dropdownDiv" class="w-full flex justify-end mr-4 z-10 relative">
    <div class="dropdown h-full relative">

        <img src="https://img.icons8.com/ios/50/000000/menu.png" class="h-20 w-20"/>

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
