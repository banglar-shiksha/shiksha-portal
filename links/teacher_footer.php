        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const submenuToggles = document.querySelectorAll('.submenu-toggle');
            submenuToggles.forEach(toggle => {
                toggle.addEventListener('click', function(event) {
                    event.preventDefault();
                    this.parentElement.classList.toggle('open');
                });
            });

            const timeElement = document.getElementById('current-time');
            function updateTime() {
                 const now = new Date();
                 timeElement.textContent = now.toLocaleDateString('en-IN', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + ' ' + now.toLocaleTimeString('en-IN');
            }
            if(timeElement){
                updateTime();
                setInterval(updateTime, 1000);
            }
        });
    </script>
</body>
</html>
