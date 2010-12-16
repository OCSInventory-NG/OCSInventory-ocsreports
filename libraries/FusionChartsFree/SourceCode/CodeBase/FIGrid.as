/*
 * FusionCharts Free v2
 * http://www.fusioncharts.com/free
 *
 * Copyright (c) 2009 InfoSoft Global (P) Ltd.
 * Dual licensed under the MIT (X11) and GNU GPL licenses.
 * http://www.fusioncharts.com/free/license
 *
 * MIT License: http://www.opensource.org/licenses/mit-license.php
 * GPL License: http://www.gnu.org/copyleft/gpl.html
 *
 * Date: 2009-08-21
 */
//--------------------------------------------------------------------------------
/*
Grid Class defined here.

We use the Grid class to render a draggable simple data table.
*/
Grid = function (depth, xPos, yPos, width, height, rows, columns, cellWidth, cellHeight, allowResize, borderColor, borderAlpha, resizeBarColor, resizeBarThickness, resizeBarAlpha) {
	// Constructor function
	// Details of parameters:
	// Depth - Level in which the entire grid would be plotted
	// xPos - Starting x Position of the grid (global perspective)
	// yPos - Starting y Position of the  grid (global perspective)
	// Width - Width of the grid to be drawn
	// Height - Height of the grid to be drawn
	// Rows - Number of rows that the grid would plot
	// Columns - Number of columns that the grid would plot
	// CellWidth (Array) - A single dimension array consisting width of each column
	// CellHeight (Array) - A single dimension array consisting required height for each row
	// allowResize (Boolean) - Whether to allow the users to resize the grid
	// borderColor, borderAlpha - Grid border properties
	// resizeBarColor, resizeBarThickness, resizeBarAlpha - Resize bar properties
	// Copy parameters to class objects
	this.depth = depth;
	this.xPos = xPos;
	this.yPos = yPos;
	this.gridWidth = width;
	this.gridHeight = height;
	this.rows = rows;
	this.columns = columns;
	this.allowResize = allowResize;
	// Initialize cellwidth and height as arrays
	this.cellWidth = new Array();
	this.cellHeight = new Array();
	if (cellWidth != null && cellWidth != undefined && cellWidth.length>0) {
		this.cellWidth = cellWidth;
		// Unshift the array to make it with base 1
		this.cellWidth.unshift(0);
	}
	if (cellHeight != null && cellHeight != undefined && cellHeight.length>0) {
		this.cellHeight = cellHeight;
		// Unshift the array to make it with base 1
		this.cellHeight.unshift(0);
	}
	this.borderColor = borderColor;
	this.borderAlpha = borderAlpha;
	// Resize bar details
	this.resizeBarColor = resizeBarColor;
	this.resizeBarThickness = resizeBarThickness;
	this.resizeBarAlpha = resizeBarAlpha;
	// Create the data container for the grid
	this.cells = new Array(this.rows);
	// Create rows x columns number of cells
	for (i=1; i<=this.rows; i++) {
		this.cells[i] = new Array();
	}
	// Now we can refer to any cell as this.cells[r][c].
	// Create a depth counter
	this.currDepth = 1;
	// Instance reference
	this.instance = this;
	// CONSTANT - Padding between the drag resize bar and column end
	this.resizePadding = 15;
	// Initialize the grid
	this.initialize();
};
//Create the cell object
Grid.cell = function(bgColor, bgAlpha, label, font, fontColor, fontSize, align, vAlign, isBold, isUnderLine, link) {
	//Each cell object represents a particular cell
	this.bgColor = bgColor;
	this.bgAlpha = bgAlpha;
	this.label = label;
	this.font = font;
	this.fontColor = fontColor;
	this.fontSize = fontSize;
	this.align = align;
	this.vAlign = vAlign;
	this.isBold = isBold;
	this.isUnderLine =isUnderLine;
	this.link = link;
	//If it's underline add the <U> tags
	if (this.isUnderLine==true)
	{
		this.label = "<U>" + this.label + "</U>";
	}
	//Add padding
	//If the label is to be left aligned, add a space on the left to give padding effect
	//Similarly, if it's to be right aligned, add padding on the right
	switch (this.align.toUpperCase()) {
	case "LEFT" :
		this.label = " "+this.label;
		break;
	case "RIGHT" :
		this.label = this.label+" ";
		break;
	}
};
Grid.prototype.initialize = function() {
	//Initialization of grid basically refers to calculating the various positions
	//and setting them as defaults if not explicitly set
	//First, we work on cell width
	//Convert all cell width to numbers
	var unallocatedCellWidth = 0;
	for (var i = 1; i<=this.columns; i++) {
		if (this.cellWidth[i] == "" || this.cellWidth[i] == undefined || IsNaN(this.cellWidth[i]) == true) {
			//Set it as 0 value
			this.cellWidth[i] = 0;
			//Increase the counter of cells whose width are unallocated
			unallocatedCellWidth++;
		} else {
			//Convert the existing value into numbers (to be on safe side during calculations)
			this.cellWidth[i] = Number(this.cellWidth[i]);
			//If the cell width is more than the width of the grid, re-set
			if (this.cellWidth[i]>this.gridWidth) {
				//Set it to equal distance
				this.cellWidth[i] = this.gridWidth/this.columns;
			}
		}
	}
	//Now, calculate the total cell width "explicitly" allotted by user
	var cellWidthAllocation = 0;
	var cellEquiWidth = 0;
	for (var i = 1; i<=this.columns; i++) {
		//Add to cellWidthAllocation
		cellWidthAllocation = cellWidthAllocation+this.cellWidth[i];
	}
	//Now, divide the unallocated cell width into unallocated cells number
	cellEquiWidth = Math.abs(this.gridWidth-cellWidthAllocation)/unallocatedCellWidth;
	//Allot this equal width to cells having 0 values
	for (var i = 1; i<=this.columns; i++) {
		if (this.cellWidth[i] == 0) {
			this.cellWidth[i] = cellEquiWidth;
		}
	}
	//Do the same for height
	//Convert all cell height to numbers
	var unallocatedCellHeight = 0;
	for (var i = 1; i<=this.rows; i++) {
		if (this.cellHeight[i] == "" || this.cellHeight[i] == undefined || IsNaN(this.cellHeight[i]) == true) {
			//Set it as 0 value
			this.cellHeight[i] = 0;
			//Increase the counter of cells whose height are unallocated
			unallocatedCellHeight++;
		} else {
			//Convert the existing value into numbers (to be on safe side during calculations)
			this.cellHeight[i] = Number(this.cellHeight[i]);
			//If the cell height is more than the height of the grid, re-set
			if (this.cellHeight[i]>this.gridHeight) {
				//Set it to equal distance
				this.cellHeight[i] = this.gridHeight/this.rows;
			}
		}
	}
	//Now, calculate the total cell height "explicitly" allotted by user
	var cellHeightAllocation = 0;
	var cellEquiHeight = 0;
	for (var i = 1; i<=this.rows; i++) {
		//Add to cellHeightAllocation
		cellHeightAllocation = cellHeightAllocation+this.cellHeight[i];
	}
	//Now, divide the unallocated cell height into unallocated cells number
	cellEquiHeight = Math.abs(this.gridHeight-cellHeightAllocation)/unallocatedCellHeight;
	//Allot this equal height to cells having 0 values
	for (var i = 1; i<=this.rows; i++) {
		if (this.cellHeight[i] == 0) {
			this.cellHeight[i] = cellEquiHeight;
		}
	}
};
Grid.prototype.setCellData = function(rowIndex, columnIndex, bgColor, bgAlpha, label, font, fontColor, fontSize, align, vAlign, isBold, isUnderLine, link) {
	//This method sets the data for a paticular grid cell
	//Create a temporary Cell Object to represent this grid's cell
	var objCell = new Grid.cell(bgColor, bgAlpha, label, font, fontColor, fontSize, align, vAlign, isBold, isUnderLine, link);
	//Set this object in the cells array
	this.cells[rowIndex][columnIndex] = objCell;
	delete objCell;
};
Grid.prototype.draw = function() {
	//This function actually draws the grid.
	//Create the container Movie clip first of all
	createEmptyMovieClip("Grid_"+this.depth, this.depth);
	//Get a reference to it
	var mcGrid = eval("Grid_"+this.depth);
	//Draw the grid background (cell & rows border line) - column wise	
	for (var i = 1; i<=this.columns; i++) {
		//Create a new container for each column	
		mcGrid.createEmptyMovieClip("Column_"+i, this.currDepth++);
		//Get reference
		var mcColumn = eval(mcGrid+".Column_"+i);
		//Move to initial position
		mcColumn.moveTo(0, 0);
		var currColWidth = this.cellWidth[i];
		var currColHeight = 0;
		//Draw the cells in it		
		for (var j = 1; j<=this.rows; j++) {
			//Set the line style for cell border
			mcColumn.lineStyle(0, parseInt(this.borderColor, 16), this.borderAlpha);
			//If it's to be filled, set the background color
			if (this.cells[j][i].bgColor != "" && this.cells[j][i].bgColor != undefined && this.cells[j][i].bgColor != null) {
				mcColumn.beginFill(parseInt(this.cells[j][i].bgColor, 16), this.cells[j][i].bgAlpha);
			}
			mcColumn.moveTo(0, currColHeight);
			mcColumn.lineTo(currColWidth, currColHeight);
			mcColumn.lineTo(currColWidth, currColHeight+this.cellHeight[j]);
			mcColumn.lineTo(0, currColHeight+this.cellHeight[j]);
			mcColumn.lineTo(0, currColHeight);
			mcColumn.endFill();
			//Height
			currColHeight = currColHeight+this.cellHeight[j];
			//End fill																					  
		}
		//x-Position the column
		var columnXPos = 0;
		for (var k = 1; k<=i-1; k++) {
			columnXPos = columnXPos+this.cellWidth[k];
		}
		mcColumn._x = columnXPos;
		delete mcColumn;
	}
	//Now, draw the resize bars only if there are more than 2 columns
	//And we have to allow resize
	if (this.columns>1 && this.allowResize == true) {
		for (var i = 2; i<=this.columns; i++) {
			//Create the resize bar containers
			mcGrid.createEmptyMovieClip("ResizeBar_"+(i-1), this.currDepth++);
			//Get the reference
			mcResizeBar = eval(mcGrid+".ResizeBar_"+(i-1));
			//Create the visible and invisible parts of the resize bar
			//The invisible part is the one which responds to mouse events (drag)
			//Visible part shows up while drawing
			mcResizeBar.createEmptyMovieClip("HitArea", 1);
			mcResizeBar.createEmptyMovieClip("Bar", 2);
			//Get reference to both
			mcRBHitArea = eval(mcResizeBar+".HitArea");
			mcRBBar = eval(mcResizeBar+".Bar");
			//Create the lines
			//Hit Area
			mcRBHitArea.lineStyle(6, 0x000000, 0);
			mcRBHitArea.moveTo(0, 0);
			mcRBHitArea.lineTo(0, currColHeight);
			//Visible Bar
			mcRBBar.lineStyle(this.resizeBarThickness, parseInt(this.resizeBarColor, 16), this.resizeBarAlpha);
			mcRBBar.moveTo(0, 0);
			mcRBBar.lineTo(0, currColHeight);
			//By default the visible line won't be visible
			mcRBBar._visible = false;
			//Set the x-position of the resize bar
			var barXPos = 0;
			for (var k = 1; k<=i-1; k++) {
				barXPos = barXPos+this.cellWidth[k];
			}
			mcResizeBar._x = barXPos;
			//Set the internal properties
			//Id specifies the column index it drags (column index on whose right side this is attached)
			mcResizeBar.id = i-1;
			//Start X specifies the start x position till where it will be allowed to drag
			mcResizeBar.startX = barXPos-this.cellWidth[i-1];
			//End X specifies the end x position till where it will be allowed to drag.
			mcResizeBar.endX = barXPos+this.cellWidth[i];
			//Center X specifies its current position
			mcResizeBar.centerX = barXPos;
			//Grid instance			
			mcResizeBar.gridInstance = this.instance;
			//Now, set the event handlers.
			mcResizeBar.onRollOver = function() {
				// Hide mouse
				Mouse.hide();
				MovResizeCursor._visible = true;
				MovResizeCursor._x = _xmouse;
				MovResizeCursor._y = _ymouse;
				this.onMouseMove = function() {
					MovResizeCursor._x = _xmouse;
					MovResizeCursor._y = _ymouse;
				};
			};
			mcResizeBar.onRollOut = function() {
				delete this.onMouseMove;
				// Show mouse
				Mouse.show();
				MovResizeCursor._visible = false;
			};
			mcResizeBar.onPress = function() {
				// Flag
				this.dragging = true;
				// Show the resize bar
				this.Bar._visible = true;
				// Start dragging
				this.startDrag(false, this.startX+this.gridInstance.resizePadding, 0, this.endX-this.gridInstance.resizePadding, 0);
			};
			mcResizeBar.onRelease = mcResizeBar.onReleaseOutside=function () {
				// Flag
				this.dragging = false;
				// Stop dragging
				this.stopDrag();
				// Set line visible false
				this.Bar._visible = false;
				// Make the size of the left column smaller
				var mcColumnLeft = eval(mcGrid+".Column_"+(this.id));
				var mcColumnRight = eval(mcGrid+".Column_"+(this.id+1));
				mcColumnLeft._width += (this._x-this.centerX);
				mcColumnRight._x += (this._x-this.centerX);
				mcColumnRight._width -= (this._x-this.centerX);
				// Update global cellwidth
				this.gridInstance.cellWidth[this.id] += (this._x-this.centerX);
				this.gridInstance.cellWidth[this.id+1] -= (this._x-this.centerX);
				// Update indexes
				this.centerX = this._x;
				this.startX = mcColumnLeft._x;
				this.endX = mcColumnRight._x+mcColumnRight._width;
				// Get reference to previous resize bar
				var mcPreviousLine = eval(mcGrid+".ResizeBar_"+(this.id-1));
				// Set its endX position - as it can now only drag upto the centerX of the currently dragged resize bar
				mcPreviousLine.endX = this.centerX;
				// Get reference to next resize bar
				var mcNextLine = eval(mcGrid+".ResizeBar_"+(this.id+1));
				// Set its startX position - as it can now only drag from the centerX of the currently dragged resize bar
				mcNextLine.startX = this.centerX;
				// Re-draw the text for this column and the next column
				this.gridInstance.drawText(this.id);
				this.gridInstance.drawText(this.id+1);
			};
		}
	}
	//Shift the grid to required x and y position
	mcGrid._x = this.xPos;
	mcGrid._y = this.yPos;
	//Set the depth index for labels
	this.labelDepth = this.currDepth+1;
	delete mcGrid;
	//Draw the text for all the columns initially
	for (var i = 1; i<=this.columns; i++) {
		this.drawText(i);
	}
};
Grid.prototype.drawText = function(columnId) {
	//This function renders the text in the grid for a particular column
	//First get the cumulative xPos of that column's center
	var columnCenterX = 0;
	//Variable to store the center y position of the row
	var rowCenterY = 0;
	for (var i = 1; i<columnId; i++) {
		columnCenterX = columnCenterX+this.cellWidth[i];
	}
	//Add to the center of current column
	columnCenterX = columnCenterX+(this.cellWidth[columnId]*.5);
	//Now iterate through all rows and create the text
	for (var i = 1; i<=this.rows; i++) {
		//If the label is a link node
		if (this.cells[i][columnId].link != "" && this.cells[i][columnId].link != undefined) {
			//Create the linked label
			this.createBoundedText(this.labelDepth+((i-1)*this.columns)+columnId, "<A HREF='"+this.cells[i][columnId].link+"'>"+this.cells[i][columnId].label+"</a>", columnCenterX, (rowCenterY+this.cellHeight[i]*0.5), this.cellWidth[columnId], this.cellHeight[i], this.cells[i][columnId].font, this.cells[i][columnId].fontSize, this.cells[i][columnId].fontColor, this.cells[i][columnId].isBold, this.cells[i][columnId].align, this.cells[i][columnId].vAlign, true);
		} else {
			//Create simple text label
			this.createBoundedText(this.labelDepth+((i-1)*this.columns)+columnId, this.cells[i][columnId].label, columnCenterX, (rowCenterY+this.cellHeight[i]*0.5), this.cellWidth[columnId], this.cellHeight[i], this.cells[i][columnId].font, this.cells[i][columnId].fontSize, this.cells[i][columnId].fontColor, this.cells[i][columnId].isBold, this.cells[i][columnId].align, this.cells[i][columnId].vAlign, true);
		}
		//Add the row height 
		rowCenterY = rowCenterY+this.cellHeight[i];
	}
};
Grid.prototype.createBoundedText = function(depth, strText, xPos, yPos, width, height, fontFamily, fontSize, fontColor, isBold, alignPos, vAlignPos, isHTML) {
	//This function creates a text field according to the parameters passed
	//Parameters
	//--------------------------
	//depth - movie clip depth - Number
	//strText - text to be displayed in the text box - string
	//xPos - x position of the text box - Number
	//yPos - y position of the text box - Number
	//fontFamily - font face of the text - string
	//fontSize - size of the text - Number
	//fontColor - color without # like FFFFDD, 000222 - string
	//isBold - bold property - boolean
	//alignPos - alignment position - "center", "left", or "right" - string
	//vAlignPos- vertical alignment position - "center", "left", or "right" - string
	//isHTML - option whether the text would be rendered as HTML or as text
	//Returns - the width,height, xPos and yPos of the textbox created
	//As an object with properties textWidth and textHeight
	//--------------------------
	//First up, we get the actual pixels (width) this text will take
	var objT = createText(50000+depth, strText, xPos, yPos, fontFamily, fontSize, fontColor, isBold, alignPos, vAlignPos, null, isHTML);
	var tWidth = objT.textWidth;
	var tHeight = objT.textHeight;
	deleteText(50000+depth);
	//Set defaults for isBold, alignPos, vAlignPos, rotationAngle, isHTML
	if (isBold == undefined || isBold == null || isBold == "") {
		isBold = false;
	}
	alignPos = getFirstValue(alignPos, "center");
	vAlignPos = getFirstValue(vAlignPos, "center");
	//By default, we render all text as HTML
	if (isHTML == undefined || isHTML == null || isHTML == "") {
		isHTML = true;
	}
	//First, create a new Textformat object
	var fcTextFormat = new TextFormat();
	//Set the properties of the text format objects
	fcTextFormat.font = fontFamily;
	fcTextFormat.color = parseInt(fontColor, 16);
	fcTextFormat.size = fontSize;
	fcTextFormat.bold = isBold;
	//Create a textProperties object which would be returned to the caller 
	//to represent the text width and height
	var LTextProperties;
	LTextProperties = new Object();
	//Create the actual text field object now. - a & b are undefined variables
	//We want the initial text field size to be flexible
	//Get a reference to it
	var mcGrid = eval("Grid_"+this.depth);
	mcGrid.createTextField("ASMovText_"+depth, depth, xPos, yPos, width, height);
	//Get a reference to the text field MC
	var fcText = eval(mcGrid+".ASMovText_"+depth);
	//Set the properties
	fcText.multiLine = true;
	fcText.selectable = false;
	fcText.html = isHTML;
	//Set the text
	if (isHTML) {
		//If it's HTML text, set as htmlText
		fcText.htmlText = strText;
	} else {
		//Else, set as plain text
		fcText.text = strText;
	}
	//Apply the text format
	fcText.setTextFormat(fcTextFormat);
	//Horizontal alignment
	//If the textwidth is more than width available, we just left align
	if (tWidth>width) {
		fcText._x = xPos-(width/2);
	} else {
		//Else we do the proper alignment
		switch (alignPos.toUpperCase()) {
		case "LEFT" :
			fcText._x = xPos-(width/2);
			break;
		case "CENTER" :
			fcText._x = xPos-(tWidth/2);
			break;
		case "RIGHT" :
			fcText._x = xPos+(width/2)-tWidth;
			break;
		}
	}
	//If the text height is greater than the height available, then just set it to center
	if (tHeight>height) {
		fcText._y = yPos-(height/2);
		break;
	} else {
		//Vertical alignment
		switch (vAlignPos.toUpperCase()) {
		case "LEFT" :
			//Left is equivalent to top (of the ypos mid line - virtual)
			//        TEXT HERE
			//---------MID LINE---------
			//       (empty space)                 
			fcText._y = yPos-height/2;
			break;
		case "CENTER" :
			//       (empty space)                 
			//---------TEXT HERE---------
			//       (empty space)                 
			fcText._y = yPos-(tHeight/2);
			break;
		case "RIGHT" :
			//Right is equivalent to bottom
			//       (empty space)                 
			//---------MID LINE---------
			//         TEXT HERE
			fcText._y = yPos+height/2-tHeight;
			break;
		}
	}
	delete fcTextFormat;
	delete fcText;
};
