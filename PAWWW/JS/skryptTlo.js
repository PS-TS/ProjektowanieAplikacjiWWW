var computed = false;
var decimal = 0;

function convert(entryform, from, to) {
    let convertfrom = from.selectedIndex;
    let convertto = to.selectedIndex;
    entryform.display.value = (entryform.input.value * from[convertfrom].value / to[convertto].value);
}

function addchar(input, character) {
    if ((character === '.' && decimal === 0) || character !== '.') {
        if (input.value === "" || input.value === "0") {
            input.value = character;
        } else {
            input.value += character;
        }

        convert(input.form, input.form.measure1, input.form.measure2);
        computed = true;

        if (character === '.') {
            decimal = 1;
        }
    }
}

function openvothcom() {
    window.open("", "Display window", "toolbar=no, directories=no, menubar=no");
}

function clear(form) {
    form.input.value = 0;
    form.display.value = 0;
    decimal = 0;
}

function changeBackground(hexNumber) {
    const contentDiv = document.getElementById('plac');
    if (contentDiv) {
        contentDiv.style.backgroundColor = hexNumber;
    }
}