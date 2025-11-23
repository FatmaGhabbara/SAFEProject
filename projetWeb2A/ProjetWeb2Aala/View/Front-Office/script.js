
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString();
        document.getElementById('currentTime').value = timeString;
    } 
    updateTime();

    document.getElementById("form").addEventListener("submit", function(e) {

        // Récupère tous les inputs du formulaire
        const inputs = this.querySelectorAll("input")&&this.querySelectorAll("textarea");

        // Vérifie s'il existe un champ vide
        for (let input of inputs) {
            if (input.value.trim() === "") {
                e.preventDefault(); // bloque l'envoi
                alert("Merci de remplir tous les champs.");
                return;
            }
        }

    });