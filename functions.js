// Vérifier si l'URL correspond à la version de développement
function checkDevSite() {
	if (window.location.hostname === "devrr.harkayn.ovh") {
	  // Créer un élément div pour la barre rouge
	  const bar = document.createElement("div");
	  
	  // Styliser la barre
	  bar.style.backgroundColor = "#600";
	  bar.style.color = "white";
	  bar.style.textAlign = "center";
	  bar.style.padding = "10px";
	  bar.style.position = "fixed";
	  bar.style.top = "0";
	  bar.style.left = "0";
	  bar.style.width = "100%";
	  bar.style.zIndex = "9999"; // Assurer que la barre est au dessus de tout
	  document.body.style.paddingTop = "50px"; // Remplacez 50 par la hauteur de votre barre
	  
	  // Ajouter du texte à la barre
	  bar.textContent = "Attention: This is the development website; some features may be incomplete or not functioning properly.";
	  
	  // Ajouter la barre au corps du document
	  document.body.prepend(bar);
	}
}

function calcIncome(cell) {
    var totalYield = parseFloat(document.getElementById('totalYield').value);
    var itemName = cell.closest('tr').querySelector('td:first-child').textContent.trim();
    var price = parseFloat(cell.getAttribute("data-price"));

    // Déplacez cette logique de condition ici, avant de vérifier si le minerai est compressé.
    var isCompressed = itemName.startsWith("Compressed ");

    if (isCompressed) {
        var uncompressedName = itemName.replace("Compressed ", "");
        var priceElement = document.querySelector(`td[data-price]:has(.tooltiptext:contains(${uncompressedName}))`);
        if(priceElement) {
            price = parseFloat(priceElement.getAttribute("data-price"));
        }
    }
    
   var income = totalYield * price * 3600;
   var formattedIncome = formatISK(income);

   var tooltipContent = "<b>"+itemName+"</b><br />"
                      + "Total yield: "+totalYield+" m³/s<br />"
                      + "Price/m³: " + price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " ISK/m³<br />"
                      + "Income: " +formattedIncome;

   // Regénérer complètement le contenu de la cellule en insérant le tooltip
   cell.innerHTML = `<div class="tooltip">${formattedIncome}<span class="tooltiptext" style="white-space: nowrap;">${tooltipContent}</span></div>`;
}

function calcAllIncome() {
    input = document.getElementById('totalYield');
    var value = parseFloat(input.value);
    if (value <= 0) {
        input.value = input.min;
    }
    var cells = document.querySelectorAll('td.calc-income');
    cells.forEach(function(cell) {
        calcIncome(cell);
    });
}

function formatISK(number) {
    if (number >= 1000000000) {
        return (number / 1000000000).toFixed(1) + 'B';
    } else if (number >= 1000000) {
        return (number / 1000000).toFixed(1) + 'M';
    } else if (number >= 10000) {
        return (number / 1000).toFixed(0) + 'K';
    } else {
        return number.toLocaleString('en-US');
    }
}

// Fonction pour charger la valeur du champ depuis le stockage local
function loadTotalYield() {
    var totalYieldInput = document.getElementById('totalYield');
    var totalYieldValue = localStorage.getItem('totalYield');
    if (totalYieldValue !== null) {
        totalYieldInput.value = totalYieldValue;
		calcAllIncome();
    }
}

// Fonction pour enregistrer la valeur du champ dans le stockage local lorsqu'elle change
function saveTotalYield() {
    var totalYieldInput = document.getElementById('totalYield');
    var totalYieldValue = totalYieldInput.value;
    localStorage.setItem('totalYield', totalYieldValue);
}

// Appel des fonctions au chargement de la page
window.addEventListener('load', function() {
    checkDevSite();
    loadTotalYield(); // Charge la valeur du champ depuis le stockage local
    var totalYieldInput = document.getElementById('totalYield');
    if (totalYieldInput) {
        totalYieldInput.addEventListener('change', saveTotalYield); // Enregistre la valeur du champ dans le stockage local lorsque celle-ci change
    }
});



function copyToClipboard(str) {
	// Create new element
	var el = document.createElement("textarea");
	// Set value (string to be copied)
	el.value = str;
	// Set non-editable to avoid focus and move outside of view
	el.setAttribute("readonly", "");
	el.style = { position: "absolute", left: "-9999px" };
	document.body.appendChild(el);
	// Select text inside element
	el.select();
	// Copy text to clipboard
	document.execCommand("copy");
	// Remove temporary element
	document.body.removeChild(el);
}

function animateCopyIcon(copyIcon) {
	// Add a class to enable the animation
	copyIcon.classList.remove('no-animation');

	// Trigger reflow to ensure initial opacity is applied before animation
	copyIcon.offsetHeight;

	// Show the checkmark animation
	copyIcon.src = 'images/checkmark.png'; // Assuming you have a checkmark image
	copyIcon.style.animation = 'fadeInOut 1s ease-out';

	// Reset the animation after a delay (1.5 seconds in this example)
	setTimeout(() => {
	  copyIcon.style.animation = 'none';

	  // Remove the class to disable the animation
	  copyIcon.classList.add('no-animation');

	  // Restore the original icon
	  copyIcon.src = 'images/copy.png';
	}, 1500);
  }

let sortDirections = {};

function sortTable(columnIndex) {
	let table, tbody, rows, switching, i, x, y, shouldSwitch;

	table = document.getElementById("myTable");
	tbody = table.getElementsByTagName("tbody")[0];

	// Initialise la direction de tri si elle n'est pas définie
	if (!sortDirections[columnIndex]) {
		sortDirections[columnIndex] = "asc";
	}

	switching = true;
	while (switching) {
		switching = false;
		rows = tbody.getElementsByTagName("tr");
		for (i = 0; i < rows.length - 1; i++) {
			shouldSwitch = false;
			x = rows[i].getElementsByTagName("td")[columnIndex];
			y = rows[i + 1].getElementsByTagName("td")[columnIndex];
			let xValue = x.innerText.toLowerCase();
			let yValue = y.innerText.toLowerCase();
			let isNumber = !isNaN(xValue) && !isNaN(yValue);
			if (sortDirections[columnIndex] === "asc") {
				if (isNumber) {
					shouldSwitch = Number(xValue) > Number(yValue);
				} else {
					shouldSwitch = xValue > yValue;
				}
			} else if (sortDirections[columnIndex] === "desc") {
				if (isNumber) {
					shouldSwitch = Number(xValue) < Number(yValue);
				} else {
					shouldSwitch = xValue < yValue;
				}
			}
			if (shouldSwitch) {
				rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
				switching = true;
				break;
			}
		}
	}
	if (sortDirections[columnIndex] === "asc") {
		sortDirections[columnIndex] = "desc";
	} else {
		sortDirections[columnIndex] = "asc";
	}
}

function sortNumericTable(columnIndex) {
	let table, tbody, rows, switching, i, x, y, shouldSwitch;
	table = document.getElementById("myTable");
	tbody = table.getElementsByTagName("tbody")[0];

	// Initialise la direction de tri si elle n'est pas définie
	if (!sortDirections[columnIndex]) {
		sortDirections[columnIndex] = "asc";
	}

	switching = true;
	while (switching) {
		switching = false;
		rows = tbody.getElementsByTagName("tr");
		for (i = 0; i < rows.length - 1; i++) {
			shouldSwitch = false;
			x = rows[i].getElementsByTagName("td")[columnIndex];
			y = rows[i + 1].getElementsByTagName("td")[columnIndex];
			let xValue = cleanCellValue(x.innerHTML);
			let yValue = cleanCellValue(y.innerHTML);
			// alert(xValue+" vs "+yValue);
			let isNumber = !isNaN(xValue) && !isNaN(yValue);
			if (sortDirections[columnIndex] === "asc") {
				if (isNumber) {
					shouldSwitch = Number(xValue) > Number(yValue);
				} else {
					shouldSwitch = xValue > yValue;
				}
			} else if (sortDirections[columnIndex] === "desc") {
				if (isNumber) {
					shouldSwitch = Number(xValue) < Number(yValue);
				} else {
					shouldSwitch = xValue < yValue;
				}
			}
			if (shouldSwitch) {
				rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
				switching = true;
				break;
			}
		}
	}
	if (sortDirections[columnIndex] === "asc") {
		sortDirections[columnIndex] = "desc";
	} else {
		sortDirections[columnIndex] = "asc";
	}
}

function cleanCellValue(cellValue) {
	// remove any child elements and span tags
	let tempElement = document.createElement("div");
	tempElement.innerHTML = cellValue;
	let spanElements = tempElement.getElementsByTagName("span");
	for (let i = spanElements.length - 1; i >= 0; i--) {
		spanElements[i].parentNode.removeChild(spanElements[i]);
	}
	let cleanValue = tempElement.textContent || tempElement.innerText || "";
	// remove whitespaces
	cleanValue = cleanValue.trim().replace(/\s+/g, "");
	return cleanValue;
}
