let select = document.getElementById('quantity');
let priceTag = document.getElementById('priceTag');

select.addEventListener('change', () => {
    priceTag.textContent = select.value * 10;
});

window.addEventListener('load', () => {
    priceTag.textContent = select.value * 10;
})