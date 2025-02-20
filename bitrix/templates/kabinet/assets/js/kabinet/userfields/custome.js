if (typeof BX.Main != 'undefined'){
BX.Main.UF.BaseType.prototype.addRow = function (fieldName, thisButton)
{

	var element = thisButton.parentNode.getElementsByTagName('span');
	if (element && element.length > 0 && element[0])
	{
		var parentElement = element[0].parentNode; // parent
		var newNode = this.getClone(element[element.length - 1], fieldName);

		if (parentElement === thisButton.parentNode)
		{
			parentElement.insertBefore(newNode, thisButton);
		}
		else
		{
			parentElement.appendChild(newNode);
		}
	}
};
}