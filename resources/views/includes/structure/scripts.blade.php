<script>
    let app = document.getElementById('app');

    window.APP_DEBUG = "{{getConfiguration('DEBUG')['config']->value}}";
    window.addEventListener('load', () => {
        app.style.display = 'block';

        /* compact menu */
        const hideMenu = document.getElementById('menu-container');
        const menuContainer = document.getElementById('icon-arrow-box');

        const compactMenuDropdowns = document.querySelectorAll('#compact-menu-dropdown');



        /**
         * This function is executed when you click on the button (icon-arrow-box) to make the menu compact or full.
         */
        if (menuContainer !== null) {

            menuContainer.addEventListener('click', function() {

                if (hideMenu.classList.contains('hide-menu')) {
                    hideMenu.classList.remove('hide-menu')

                    /**
                     * change classes for the icon-item-box for the compact and complete menu
                     */
                    if (compactMenuDropdowns !== null) {
                        compactMenuDropdowns.forEach(ul => {

                            ul.classList.remove('compact-menu-dropdown')
                            ul.classList.add('full-menu-dropdown');
                        });
                    }

                } else {
                    hideMenu.classList.add('hide-menu');

                    /**
                     * change classes for the icon-item-box for the compact and complete menu
                     */
                    if (compactMenuDropdowns !== null) {
                        compactMenuDropdowns.forEach(ul => {
                            ul.classList.remove('full-menu-dropdown')
                            ul.classList.add('compact-menu-dropdown');
                        });
                    }
                }
            });
        }



        /* show  */
        const divIconFloatMenu = document.getElementById('div-float-menu');
        const iconFloatMenu = document.getElementById('icon-float-menu');
        const midSeccion = document.getElementById('mid-seccion');

        /*
         * change icon menu movil
         */

        const changeIconMenuMovil = () => {

            if (iconFloatMenu.classList.contains('fa-bars')) {

                iconFloatMenu.classList.remove('fa-solid')
                iconFloatMenu.classList.remove('fa-bars')

                iconFloatMenu.classList.add('fa-regular');
                iconFloatMenu.classList.add('fa-circle-xmark');

                /**
                 * The icon has a white background but is removed when opening the menu,
                 *  adding a class (remove-background)
                 */
                divIconFloatMenu.classList.add('remove-background');

            } else {
                iconFloatMenu.classList.remove('fa-regular')
                iconFloatMenu.classList.remove('fa-circle-xmark')

                iconFloatMenu.classList.add('fa-solid');
                iconFloatMenu.classList.add('fa-bars');

                /**
                 * the icon has a white background
                 * and alternates between adding and removing the class (remove-background)
                 */
                divIconFloatMenu.classList.remove('remove-background');
            }
        }


        /*
         * show menu movil
         */
        const showMenuMovil = () => {
            if (midSeccion.classList.contains('show-menu-movil')) {
                midSeccion.classList.remove('show-menu-movil')
            } else {
                /*
                 * I remove the hide-menu class because,
                 * this is a class that is applied in desktop mode and we do not want that behavior in mobile mode.
                 */
                hideMenu.classList.remove('hide-menu');

                midSeccion.classList.add('show-menu-movil');
            }
        }

        if (divIconFloatMenu !== null) {
            divIconFloatMenu.addEventListener('click', function() {
                changeIconMenuMovil()
                showMenuMovil();
            });
        }

    });

    function openDropdown(id){

        const clickEvent = new MouseEvent("click", {
        });

        let toOpen = document.getElementById('link_icon_' + id);
        toOpen.dispatchEvent(clickEvent);
    }

</script>
