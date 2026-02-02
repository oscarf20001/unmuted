<form id="ticketWrapper">
    <!-- Vorname -->
    <div class="input-field vorname">
        <input type="text" id="vorname" name="vorname" required="">
        <label for="vorname">Vorname:<sup>*</sup></label>
    </div>

    <!-- nachname -->
    <div class="input-field nachname">
        <input type="text" id="nachname" name="nachname" required="">
        <label for="nachname">Nachname:<sup>*</sup></label>
    </div>

    <!-- email -->
    <div class="input-field email">
        <input type="email" id="email" name="email" required="">
        <label for="email">Email:<sup>*</sup></label>
    </div>

    <!-- day -->
    <div class="day">
        <p>Vorstellung am:<sup>*</sup></p>
        <input type="radio" id="11-03-2026-19:00:00" name="dayOfPresentation" required="" value="11-03-2026 19:00:00">
        <label for="11-03-2026-19:00:00">MI. 11.03.2026, 19:00 Uhr</label>
        <br>
        <input type="radio" id="12-03-2026-19:00:00" name="dayOfPresentation" required="" value="12-03-2026 19:00:00">
        <label for="12-03-2026-19:00:00">DO. 12.03.2026, 19:00 Uhr</label>
    </div>

    <!-- tickets -->
    <div class="input-field quantity">
        <select name="quantity" id="quantity" required>
            <option value="1">1 Ticket</option>
            <option value="2">2 Tickets</option>
            <option value="3">3 Tickets</option>
            <option value="4">4 Tickets</option>
            <option value="5">5 Tickets</option>
        </select>
        <label for="quantity">Tickets:<sup>*</sup></label>
    </div>

    <!-- price -->
    <div class="price">
        <p>Zu bezahlender Betrag: <span id="priceTag">10</span>€</p>
    </div>

    <!-- submit -->
    <div class="input-field submit">
        <input type="submit" id="submit" name="submit" required="">
    </div>
</form>