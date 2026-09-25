(function ($) {
    "use strict";

    var musicAudio = null;
    var musicButton = null;
    var musicStartSeconds = 8;
    var currentInvitation = null;

    function setMusicButton(isPlaying) {
        if (!musicButton) {
            return;
        }

        var icon = musicButton.querySelector("i");
        var label = musicButton.querySelector("span");

        musicButton.classList.toggle("is-playing", isPlaying);
        musicButton.setAttribute("aria-pressed", isPlaying ? "true" : "false");
        musicButton.setAttribute("aria-label", isPlaying ? "Pausar musica" : "Reproducir musica");

        if (icon) {
            icon.className = isPlaying ? "fa fa-pause" : "fa fa-play";
        }

        if (label) {
            label.textContent = isPlaying ? "Pausar" : "Musica";
        }
    }

    function playWeddingMusic() {
        if (!musicAudio || !musicAudio.paused) {
            return;
        }

        if (musicAudio.currentTime < musicStartSeconds) {
            try {
                musicAudio.currentTime = musicStartSeconds;
            } catch (error) {
                window.console.warn("No fue posible adelantar la cancion", error);
            }
        }

        musicAudio.play().then(function () {
            setMusicButton(true);
        }).catch(function () {
            setMusicButton(false);
        });
    }

    function renderCountdown() {
        var clock = $("#clock");
        var dateValue = $("body").data("wedding-date");

        if (!clock.length || !dateValue) {
            return;
        }

        var target = new Date(dateValue).getTime();

        function update() {
            var distance = target - Date.now();

            if (distance <= 0) {
                clock.html('<div class="box"><div><div class="time">00</div><span>Dias</span></div></div><div class="box"><div><div class="time">00</div><span>Horas</span></div></div><div class="box"><div><div class="time">00</div><span>Min</span></div></div><div class="box"><div><div class="time">00</div><span>Seg</span></div></div>');
                return;
            }

            var days = Math.floor(distance / 86400000);
            var hours = Math.floor((distance % 86400000) / 3600000);
            var minutes = Math.floor((distance % 3600000) / 60000);
            var seconds = Math.floor((distance % 60000) / 1000);

            clock.html(
                '<div class="box"><div><div class="time">' + days + '</div><span>Dias</span></div></div>' +
                '<div class="box"><div><div class="time">' + String(hours).padStart(2, "0") + '</div><span>Horas</span></div></div>' +
                '<div class="box"><div><div class="time">' + String(minutes).padStart(2, "0") + '</div><span>Min</span></div></div>' +
                '<div class="box"><div><div class="time">' + String(seconds).padStart(2, "0") + '</div><span>Seg</span></div></div>'
            );
        }

        update();
        window.setInterval(update, 1000);
    }

    function getInvitationCode() {
        var params = new URLSearchParams(window.location.search);
        return (params.get("inv") || params.get("codigo") || "").trim().toUpperCase();
    }

    function replaceSelectOptions(select, maxValue, label) {
        if (!select) {
            return;
        }

        var max = Math.max(0, parseInt(maxValue, 10) || 0);
        select.innerHTML = "";

        var placeholder = document.createElement("option");
        placeholder.disabled = true;
        placeholder.textContent = label;
        select.appendChild(placeholder);

        for (var index = 0; index <= max; index += 1) {
            var option = document.createElement("option");
            option.value = String(index).padStart(2, "0");
            option.textContent = String(index).padStart(2, "0");
            select.appendChild(option);
        }

        select.value = String(max).padStart(2, "0");
    }

    function applyInvitation(invitation) {
        var card = document.getElementById("guest-invite-card");
        var name = document.getElementById("guest-invite-name");
        var passes = document.getElementById("guest-invite-passes");
        var nameInput = document.getElementById("name");
        var adultsSelect = document.querySelector('[name="adults"]');
        var childrenSelect = document.querySelector('[name="children"]');

        currentInvitation = invitation;

        if (card && name && passes) {
            name.textContent = invitation.name;
            passes.textContent = "Pases asignados: " + invitation.adults + " adultos y " + invitation.children + " ninos.";
            card.hidden = false;
        }

        if (nameInput && !nameInput.value) {
            nameInput.value = invitation.name;
        }

        replaceSelectOptions(adultsSelect, invitation.adults, "Adultos");
        replaceSelectOptions(childrenSelect, invitation.children, "Ninos");
    }

    function loadPersonalInvitation() {
        var code = getInvitationCode();

        if (!code) {
            return;
        }

        fetch("invitado.php?code=" + encodeURIComponent(code), { cache: "no-store" })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("Invitado no encontrado");
                }
                return response.json();
            })
            .then(applyInvitation)
            .catch(function (error) {
                window.console.warn("No fue posible personalizar la invitacion", error);
            });
    }

    function bindSinglePageNav() {
        $('.navigation-holder a[href^="#"], .wedding-footer a[href^="#"], .wedding-actions a[href^="#"]').on("click", function () {
            $(".navigation-holder").removeClass("slideInn");
            $("body").removeClass("body-overlay");
        });
    }

    function bindRsvp() {
        $("#rsvp-form").on("submit", function (event) {
            event.preventDefault();

            var formData = new FormData(this);
            var payload = {};
            var whatsappNumber = "523333916461";

            formData.forEach(function (value, key) {
                payload[key] = value;
            });

            var adults = payload.adults || "No especificado";
            var children = payload.children || "No especificado";
            var message = [
                "Hola, quiero confirmar mi asistencia a la boda de Hector y Cynthia.",
                "",
                "Nombre: " + (payload.name || ""),
                "Asistencia: " + (payload.attendance || ""),
                "Adultos: " + adults,
                "Ninos: " + children,
                "Mensaje: " + (payload.message || "Sin mensaje")
            ];

            if (currentInvitation) {
                message.splice(2, 0, "Codigo: " + currentInvitation.code);
                message.splice(3, 0, "Invitacion para: " + currentInvitation.name);
                message.splice(4, 0, "Pases asignados: " + currentInvitation.adults + " adultos y " + currentInvitation.children + " ninos");
                message.splice(5, 0, "");
            }

            message = message.join("\n");

            var confirmation = {
                code: currentInvitation ? currentInvitation.code : "",
                invitation_name: currentInvitation ? currentInvitation.name : "",
                name: payload.name || "",
                attendance: payload.attendance || "",
                adults: parseInt(payload.adults, 10) || 0,
                children: parseInt(payload.children, 10) || 0,
                message: payload.message || ""
            };

            try {
                var saved = JSON.parse(window.localStorage.getItem("wedding_rsvp") || "[]");
                saved.push({
                    createdAt: new Date().toISOString(),
                    data: payload
                });
                window.localStorage.setItem("wedding_rsvp", JSON.stringify(saved));
            } catch (error) {
                window.console.warn("No fue posible guardar RSVP localmente", error);
            }

            fetch("rsvp.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(confirmation),
                keepalive: true
            }).catch(function (error) {
                window.console.warn("No fue posible guardar la confirmacion", error);
            });

            window.location.href = "https://wa.me/" + whatsappNumber + "?text=" + encodeURIComponent(message);

            $("#rsvp-message")
                .addClass("is-visible")
                .text("Se abrira WhatsApp para enviar tu confirmacion.");

            this.reset();
            $("#attend").prop("checked", true);
        });
    }

    function bindMusicPlayer() {
        musicAudio = document.getElementById("wedding-song");
        musicButton = document.querySelector(".wedding-music-toggle");

        if (!musicAudio || !musicButton) {
            return;
        }

        musicAudio.addEventListener("error", function () {
            musicButton.classList.add("is-unavailable");
            musicButton.setAttribute("aria-label", "Cancion pendiente");
            musicButton.disabled = true;

            var icon = musicButton.querySelector("i");
            var label = musicButton.querySelector("span");

            if (icon) {
                icon.className = "fa fa-music";
            }

            if (label) {
                label.textContent = "Cancion pendiente";
            }
        });

        musicButton.addEventListener("click", function () {
            if (musicAudio.paused) {
                playWeddingMusic();
            } else {
                musicAudio.pause();
                setMusicButton(false);
            }
        });
    }

    function bindInvitationCover() {
        var cover = document.getElementById("wedding-cover");
        var button = document.getElementById("open-invitation");

        if (!cover || !button) {
            return;
        }

        button.addEventListener("click", function () {
            cover.classList.add("is-hidden");
            cover.setAttribute("aria-hidden", "true");
            playWeddingMusic();
        });
    }

    $(function () {
        loadPersonalInvitation();
        renderCountdown();
        bindSinglePageNav();
        bindRsvp();
        bindMusicPlayer();
        bindInvitationCover();
    });
})(jQuery);
