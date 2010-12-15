/*
 *    @author: FusionCharts Team
 *    @description: FusionCharts DOM Manipulation script
 *
 *    @publish: August 28, 2009
 *    @version: 1.1.0 (build: 48)
 */

/*
 *  ChangeLog:
 *  ==========
 *
 *  1.1.0 - 30/04/2009:
 *  Made changes in architecture to fix unique id
 *  Made changes in the way XML Islands are created
 *  Fixed bug and escaped dataURL to support multiple parameters
 *  Refactoring of some parameters.
 *  Default container class name change
 *  Addition of 'version' property
 *  Added support for addition having FusionChartsDOM.min.js as filename
 *  Sending FusionChartsDOM Invoker version to chart (internal)
 *  QA1 Fixes
 *  21: Added ClassName forcibly for IE compatibility.
 *  27: Deprecated alertOnErrors and allowed accepting 0 or 1 from settings.
 *  31: Used the true attribute 'lang'.
 *  43: Added PBarLoading text that was missed out last time.
 *  52: Fixed double-dash issue of XML parsing for IE.
 *
 *  QA47 Fixes
 *  89-95: Chart Messages now works from parameters
 *
 *  1.0.2 - 05/02/2009:
 *  Added advanced RegEx parsing of CDATA
 *  Support for ExportComponent Alias
 *
 *  1.0.1 - 05/02/2009:
 *  Added support for non-chart objects such as 'components'
 *
 *  0.6.1 - 30/12/2008:
 *  Bug Fix
 *
 *  0.6 - 15/12/2008:
 *  Added FileName Friendly lookup Table 'Alias'
 *
 *  0.5 - 17/10/2008:
 *  Added processing of chart querystrings
 *
 *  0.4.1 - 16/10/2008:
 *  Changed "attributes" in <scripts> tag to "parameters"
 *
 *  0.4 - 01/10/2008:
 *    Made attributes case insensitive
 *
 *  0.3 - 30/09/2008:
 *	  Final release candidate with global settings
 *
 *  0.2 - 26/09/2008:
 *   Had to change the entire process for JS due to IE incapabilities!
 *
 *  0.1 - 23/09/2008:
 *    First Build. Pray it works!
 *
 */

/* Check whether FusionCharts main object is already declared or not.
 *
 * Debug Info: In case you get an error, make sure -
 * 1. FusionChartsDOM.js (this file) is included AFTER the main FusionCharts.js
 *    file is included.
 * 2. If the parent script has any modifications or errors, the main object may
 *    be defined
 */
if(typeof infosoftglobal == 'undefined' ||
    typeof infosoftglobal.FusionCharts == 'undefined')
{
    // this string is in variable so that repeatations are not caused
    var ErrorMsg = "FusionChart Object was not found. Verify script inclusions.";
    alert(ErrorMsg); throw ErrorMsg;
}



// =============================================================================
// == Global Objects ===========================================================
// =============================================================================

// Create FusionDOM class and map an easy to use variable to this as this is too
// long of a name to be used as a workset
infosoftglobal.FusionChartsDOM = {};
var FusionChartsDOM = infosoftglobal.FusionChartsDOM; // henceforth FD becomes the ref object


var _FCD = infosoftglobal.FusionChartsDOM; // internal coding variable

/**
 *  Version of this JS [major, minor, revision, build]
 */
_FCD.version = [1, 1, 0, 41];

/**
 * Store and retrieve settings that are used by the FusionCharts DOM object
 * User can modify this section for their use.
 */
_FCD.Settings =
{
    // errors can be enabled/suppressed if required
    debugmode: false,

    // the attributes supported in chart <tag> with their default values in
    // specific order of passing on to the fusioncharts wrapper
    // DEV NOTE: ANY NEW PROPERTY HERE, MUST BE REPLICATED IN "probeAttributes"
    // method twice
    parameters : {
        chartid: 			'', // programmatically generated ()no-value needed)
        charttype: 			'Column2D',
        width: 				'400',
        height: 			'300',
        debugmode: 			'0',
        registerwithjs: 	'0', // v3 only
        backgroundcolor: 	'',
        scalemode: 			'noScale',
        lang:               '',
        detectflashversion: '',
        autoinstallredirect: '',
        chartversion: 		'free', // free or v3
        swfpath: 			'FusionCharts/',
        dataurl: 			'',
        swfuri: 			'' // final swf path thats generated (do not change)
    }, xparameters: {},

    // enable FD to attach itself to window.onload to render all graphs on load
    renderonload: true,

    // the message displayed before the charts are rendered
    loadingmessage: 'Chart not loaded.',

    // class applied to every container dom division created
    containerclassname: 'fusioncharts_container',

    // when set to false, if no chartId is specified, the object will raise
    // error
    autochartid: true

};
//DEBUG: _FCD.xSettings = { };

/**
 * String resources; Separated from JS for language localization and js
 * compression.
 *  DO NOT MANUALLY PUT IN ANY PROPERTY IN HERE. THE BODY DEFINITION OF THIS
 *  OBJECT IS AT DOCUMENT END
 */
_FCD.R = { };

/**
 * Common runtime/library snippet functions that will be repeatedly and
 * extensively used in this script. All cross-browser functions should be
 * implemented here, so that debugging is faster.
 *  DO NOT MANUALLY PUT ANY PROPERTY IN HERE. THE BODY DEFINITION OF THIS
 *  OBJECT IS AT DOCUMENT END
 */
_FCD.L = { };

// =============================================================================
// == Public Functions and Variables ===========================================
// =============================================================================
// Users are recommended NOT to perform any editing beyond this point.


/* Array that holds global Node objects
 * It should cointain the properties: (as per release 0.1)
 * id, parameters, chartObject, swfObject, container, renderState, sourceNode
 */
_FCD.Nodes = Array();

/*
 *  The initialization function that is called upon page load
 *  Client-constructor
 */
_FCD.Constructor = function()
{
    // Parse all the default settings passed via the script tag
    _FCD.parseSettings();

    // The work of this constructor is very simple:
    // It calls the parseDOM method that parses all the <tags> and adds them
    // to the _FCD.Nodes array.
    _FCD.parseDOM();

    // In case "renderOnLoad" is enabled in settings, then call the RenderCharts
    // method with forcedUpdate marked as true
    var $1 = _FCD.Settings.renderonload.toString();
    if($1 == 'true' || $1 == '1') _FCD.RenderAllCharts(true);
};

/*
 * this property is updated upon chart population. This helps to get the index
 * of a node from its char id
 * @param: key integer
 */
_FCD.indexOf = function(key)
{
    return _FCD._indexOf[key];
};

/*
 * Renders charts within the <graph> tag that are in the FCD.Nodes array
 * @param: force boolean "force already rendered charts to re-render"
 */
_FCD.RenderAllCharts = function(force)
{
    for(var i=0; i<_FCD.Nodes.length; i++)
    {
        // check whether the link is marked as already rendered
        // if not, then only proceed
        if( (_FCD.Nodes[i].renderState == true)
            && !force ) continue;
        _FCD.RenderChart(i); // call the updater function to replace proper html
    }
};

/*
 * Update the HTML / re-render the chart-item specified by the arrayIndex
 * It simply replaces the innerHTML of the container with the html returned by
 * the chartObject
 * @param index int
 */
_FCD.RenderChart = function(index)
{
    _FCD.Nodes[index].container.style.display = ''; // unhide
    _FCD.Nodes[index].container.innerHTML =
        _FCD.Nodes[index].chartObject.getSWFHTML();

    // call fusionchartsutil to get access to chart object
    _FCD.Nodes[index].swfObject =
    infosoftglobal.FusionChartsUtil.getChartObject(_FCD.Nodes[index].id);

    _FCD.Nodes[index].renderState = true; // mark rendered
};


// ============================================================================
// == Private Functions and Variables =========================================
// ============================================================================
// Users are NOT to perform any editing at this point.

/*
 * This is an internal private propert, that holds a list of index w.r.t chartId
 */
_FCD._indexOf = {};
var watch1;
/*
 * This function parses the DOM tree and correspindingly populates the
 * _FCD.Nodes array.
 * During the process, it does the required modifications w.r.t. IE.
 * It converts the custom tags into xmlIslands and adds all these data to
 * the Nodes array.
 */
_FCD.parseDOM = function()
{
    // deletes the charts from objects completely.
    // this is risky in case the creations fail
    _FCD.Nodes = Array();

    // get all <tags> and calls the 'process' to perform modifications
    var tagItems = _FCD.L.tags( _FCD.R.fcTag );
    var i; // initialize counter (separate init so that this var can be reused)
    var newNode;

    for(i=0; i<tagItems.length; i++)
    {
        newNode = _FCD.processTag(tagItems[i]);
        _FCD.Nodes.push( newNode );
        _FCD._indexOf[newNode.id] = i; // store keys in an array
    }

    // perform process on source nodes and set corresponding dataxml
    var sourceNodes = _FCD.processSourceNodes();

    for(i=0; i<sourceNodes.length; i++)
    {
        // adding reference to source node
        _FCD.Nodes[i].sourceNode = sourceNodes[i];

        // load data from  source
        if(_FCD.Nodes[i].parameters.dataurl)
        {
            _FCD.Nodes[i].chartObject.setDataURL(escape(_FCD.Nodes[i].parameters.dataurl));
        }
        else
        {
            var xmlString = _FCD.loadEmbeddedData(
                _FCD.Nodes[i].sourceNode );

            if(!xmlString)
            {
                // error/alert not raised in case the charttype is not a chart
                if(!_FCD.R.chartExclusionMeta[_FCD.Nodes[i].parameters.charttype.toLowerCase()])
                    _FCD.notify(_FCD.R.errorNoValidData);
            // no need to throw error here. just notify
            }
            else
            {
                _FCD.Nodes[i].chartObject.setDataXML(xmlString);
            }
        }

        // DEVNOTE: IN CASE OF Internet Explorer the container links are lost upon
        // processing of source nodes. hence they are updated.
        if(_FCD.L.isIE)
        {
            _FCD.Nodes[i].container =
            _FCD.L.get(_FCD.Nodes[i].id
                +'_'+_FCD.R.containerTagName);
        }
    }
};


/*
 * This function processes each custom chart tag and creates a FD Node out of it.
 * @param: tagObj object "HTML node received from getElementsByTagName"
 * @return property
 */
_FCD.processTag = function(tagObj)
{
    // create the list of attributes. the probeAttributes method returns
    // all the specified attributes or the default value
    var newAttributes = _FCD.probeAttributes(tagObj); // read all required attributes

    // create a unique chart id in case id is not specified
    if(!newAttributes.chartid)
    {
        var $1 = _FCD.Settings.autochartid.toString();
        if( $1 == 'true' || $1 == '1')
            newAttributes.chartid = _FCD.R.idPrefix + _FCD.L.uniqueId();
        else // in case autochartid is off, throw an error
        {
            _FCD.notify(_FCD.R.errorNoChartId);
            throw _FCD.R.errorNoChartId;
        }
    }

    // compute full swf uri in case not provided
    if(newAttributes.swfuri=='') {
        newAttributes.swfuri = newAttributes.swfpath +
        _FCD.getSWFName(newAttributes.charttype, newAttributes.chartversion);
    }
    // create a division and append it to the required place
    var containerNode = _FCD.L.getNew(_FCD.R.containerTagName,
        'id='+newAttributes.chartid+'_'+_FCD.R.containerTagName,
        'style=display:none', // hidden (to avoid layout errors before load)
        'class='+_FCD.Settings.containerclassname);

    containerNode.className = _FCD.Settings.containerclassname;
    containerNode.innerHTML = _FCD.Settings.loadingmessage;
    tagObj.parentNode.insertBefore(containerNode, tagObj); // append to DOM

    // create the chart object from the FCJS wrapper
    var newChart = _FCD.createChart(newAttributes);

    // get all chartparams and add to newchart
    var chartParams = _FCD.probeParameters(tagObj);
    for(var aParam in chartParams)
    {
        newChart.addParam(aParam, chartParams[aParam]);
        // also add that param to attributes list for easy access
        newAttributes[aParam] = chartParams[aParam];
    }

    // get all chartvariables and add to newchart
    var chartVars = _FCD.probeVariables(tagObj);
    for(var aVar in chartVars)
    {
        newChart.addVariable(aVar, chartVars[aVar]);
        // also add that vars to attributes list for easy access
        newAttributes[aVar] = chartVars[aVar];
    }

    // add fusionchartsdom version
    newChart.addVariable('FusionChartsDOM', _FCD.version.toString());
    
    return {
        id: newAttributes.chartid,
        parameters: newAttributes,
        chartObject: newChart, 
        swfObject: null,
        container: containerNode,
        renderState: false,  
        sourceNode: null
    };
};

/*
 * This function reads the arguments from DOM node and creates a chart
 * @param: $1 property "list of attributes for the new chart"
 * @return: FusionChart
 */
_FCD.createChart = function($1)
{

    // put it in try block to trap externel errors from fcjs wrapper
    try
    {
        // create a chart from node attributeListute values that we just excavated
        return new FusionCharts(
            $1.swfuri,
            $1.chartid,
            $1.width,
            $1.height,
            $1.debugmode,
            $1.registerwithjs,
            $1.backgroundcolor,
            $1.scalemode,
            $1.lang,
            $1.detectflashversion,
            $1.autoinstallredirect );
    }
    catch (er)
    {
        _FCD.notify(_FCD.R.errorUnexpected + er.toString());
        throw _FCD.R.errorUnexpected + er.toString();
    }
};

/*
 * This function returns an array of sourceNodes from <tags> after doing browser-specific
 * conversions. In case if IE, it converts to xmlIslands
 * @return: object "In case any browser specific modifications are rewuired it
 *                  sends the modified node(xmlIsland) else nodeList"
 */
_FCD.processSourceNodes = function()
{
    // do common initial routines
    var allTags = _FCD.L.tags( _FCD.R.fcTag );

    // loop through all fusioncharts
    for(var c=0; c<allTags.length; c++)
    {
        // set display style as none, to hide elements for
        // some browsers
        allTags[c].style.display='none';
    }

    // reduce this function with lighterones depending upon the browser used
    if(_FCD.L.isIE)
    {
        // append a dummy xmlisland so that all fc tags can be appended here
        var xmlIsland = _FCD.L.getNew('xml', 'style=display:none');
        document.body.appendChild(xmlIsland);

        // a common regex to pick up fc tags from a string
        var fcRegEx = new RegExp('<'+_FCD.R.fcTag+'[\\s\\S]+?<\/'+_FCD.R.fcTag+'>', 'ig');
        
        // TODO: Parse fusioncharts using single regex
        // 
        // extract all FCD Tags
        // not to extract tags within HTML comments / SCRIPT / Style
        // not to remove comments / anything within FCD tags
        // best of luck
        // alternatively doing the above in three pass
        // pass one: remove script/style
        var xmlString = document.body.innerHTML
            .replace(/<script[\s\S]+?<\/script>|<style[\s\S]+?<\/style>/ig, '');

        // pass two: get all comments and remove those comments that have
        // fusioncharts tag in them
        var $1 = xmlString.match(/<!--[\s\S]+?-->/g);
        if($1) // check if any tag was returned at all
        {
            for(var i=0; i<$1.length; i++)
            {
                if($1[i].search(fcRegEx) > 0) {
                    xmlString = xmlString = xmlString.replace($1[i], '');
                } else {
                    xmlString = xmlString.replace($1[i],
                        '<!--'+$1[i].replace(/^<!--|-->$/g,'')
                        .replace(/--/g,'&#45;&#45;')+'-->');
                }
            }
        }

        // pass three: pick the final fusioncharts tags from xmlString
        xmlString = xmlString.match(fcRegEx);
        
        // in case no xml is matched, returns nullstring
        xmlString = xmlString ? xmlString.join('') : '';

        if(xmlIsland.loadXML('<root>'+xmlString+'</root>'))
        {
            allTags = xmlIsland.firstChild.childNodes;
        } else {
            // notify that inline data not loaded
            _FCD.notify(_FCD.R.errorInline);
        }
    }
    else
    {
        // self-redefinition helps to reduce browser load!
        allTags = _FCD.L.tags( _FCD.R.fcTag );
    }

    // self-redefinition helps to reduce browser load!
    _FCD.processSourceNodes = function()
    {
        return allTags;
    };
    return allTags;
};


/*
 * This function fetches chart data when embedded as CDATA
 * @param: sourceNode object "The node from where to load data"
 */
_FCD.loadEmbeddedData = function(sourceNode)
{
    try {
        return ( (_FCD.L.isIE) ? sourceNode.childNodes[0].childNodes[0].xml :
            _FCD.L.tags('data', sourceNode)[0].innerHTML)
            .replace(/.+CDATA\[\s*?|\s*?\]\]\s*?\-\-\>\s*?/g,'');
    }
    catch (e)
    {
        return '';
    }
};

/*
 * Looks for the required list of attributes in the DOM object and when not
 * found, returns a default value
 * @param: obj object "Node whose attributes needs be probed"
 * @return object
 */
_FCD.probeAttributes = function(obj)
{
    var returnObj = {};
    for(var $1 in _FCD.Settings.parameters)
    {
        returnObj[$1] =
        _FCD.L.getAttribute(obj, $1) ||
        _FCD.Settings.parameters[$1];
    }
    return returnObj;
};

/*
 * Looks for the required list of swf variables in the DOM object.
 * @param: obj object "Node whose attributes needs be probed"
 * @return object
 */
_FCD.probeVariables = function(obj)
{
    // @since 1.1 ChartVariables also includes parameters marked as
    // chart variables

    var returnObj = {};
    var $1 = _FCD.R.chartVariables;
    var $2, $3;

    for(var c=0; c<$1.length; c++)
    {
        // make the attribute name case-safe
        $3 = $1[c].toLowerCase();
        $2 = _FCD.L.getAttribute(obj, $3);

        // check whether extra parameters are defined somewhere
        if($2==null) {
            $2 = _FCD.Settings.xparameters[$3];
        }
        if($2 != null) returnObj[$1[c]] = $2;
    }

    return returnObj;
};

/*
 * Looks for the required list of parameters for chart and also makes sure
 * that values from parameters and reservedAttributes are not added
 * @param: obj object "Node whose attributes needs be probed"
 * @return array "new object parameters"
 */
_FCD.probeParameters = function(obj)
{   
    // it is a three stage process.
    // iterate through attributes, exclude constructor attributes, exclude
    // reserved keywords and then add the remaining to array
    var returnObj = {};
    var c; // counter
    // DEVNOTE: Our great IE returns dead parameters, so create an array of
    // valid parameters
    for(c=0; c < obj.attributes.length; c++)
    {
        // do not parse invalid attributes
        if(!obj.attributes[c].specified) continue;
        
        // append valid values to proplist
        returnObj[obj.attributes[c].nodeName.toLowerCase()] =
        obj.attributes[c].nodeValue;
    }

    // FF is case-insenstitive for xhtml (which is w3c correct), so indivdually parse
    // reserved array and parameters
    var $1;
    for($1 in _FCD.Settings.parameters)
    {
        $1 = $1.toLowerCase();
        if(typeof returnObj[$1] == 'undefined') continue;
        delete returnObj[$1];
    }

    // remove reserved attributes
    var $2 = _FCD.R.reservedAttributes;
    for(c=0; c<$2.length; c++)
    {
        if(typeof returnObj[$2[c]] == 'undefined') continue;
        delete returnObj[$2[c]];
    }

    // remove flashvars
    $2 = _FCD.R.chartVariables;
    for(c=0; c<$2.length; c++)
    {
        if(typeof returnObj[$2[c]] == 'undefined') continue;
        delete returnObj[$2[c]];
    }
    return returnObj;
};

/*
 * This method parses the settings provided at the fcSettings
 */
_FCD.parseSettings = function()
{
    // locate all scripts and then find out which is the FCD script
    var scriptTags = _FCD.L.tags('script');
    var thisScript, $2=false; // tmpVar2 = file found flag

    for(var j=0; j<_FCD.R.jsFileName.length; j++)
    {
        for(var i=0; i<scriptTags.length; i++)
        {
            if(scriptTags[i].src.length - (_FCD.R.jsFileName[j]).length
                - scriptTags[i].src.toLowerCase().indexOf(_FCD.R.jsFileName[j])
                == 0)
                {
                thisScript = scriptTags[i];
                $2 = true;
                break; // when script is found, skip searching
            }
        }
        if($2 == true) {
            break;
        }
    }

    // in case parsing failed, simply exit
    if(!thisScript) return;

    // convert the user settings into properties and sync each of them
    // the process is simple: eval the attributes from json to js properties
    // and then sync matching items with existing properties
    try
    {
        eval('var tm=null;'); // store parsed properties here
        eval('tm={'
            + (thisScript.getAttribute(_FCD.R.userSettingsTagPrefix
                + 'settings')||'')+'};');
        
        _FCD.Settings =
        _FCD.syncProperties(_FCD.Settings, tm,
            _FCD.xSettings);

        eval('tm={'
            + (thisScript.getAttribute(_FCD.R.userSettingsTagPrefix
                + 'parameters')||'')+'};');

        _FCD.Settings.parameters =
        _FCD.syncProperties(_FCD.Settings.parameters, tm,
            _FCD.Settings.xparameters);
        tm = null; // clean memory
    }
    catch (e)
    {
        _FCD.notify(_FCD.R.errorParseSettings +
            "\nDebug Info: " +e);
    }
};

/*
 * This method synchronises properties from source to target
 * @param: target property "the property whose data is to be updated"
 * @param: source property "the property from which to take data"
 * @param: extra  property "extra data from source is dumped here"
 * @return: property "updated target property"
 */
_FCD.syncProperties = function(target, source, extra)
{
    var $1;

    // in case there is an extra defined, then the routine changes to
    // incorporate extra items
    for($1 in source)
    {
        if(typeof target[$1.toLowerCase()] != 'undefined') {
            target[$1.toLowerCase()] = source[$1];
        }
        else {
            extra[$1.toLowerCase()] = source[$1];
        }
    }

    return target;
};

/*
 * Returns appropriate filename for respective chartType aliases
 */

_FCD.getSWFName = function(cTyp, cVer)
{
    cTyp = cTyp.toLowerCase(); cVer = cVer.toLowerCase();

    // validate at attribute level
    if(!_FCD.R.chartAliasMeta[cVer]) {
        _FCD.notify(_FCD.R.errorAlias
            + "\nDebug Info: Invalid parameter: ChartVersion", true);
    }
    if(!_FCD.R.chartAlias[cTyp]) {
        _FCD.notify(_FCD.R.errorAlias
            + "\nDebug Info: Invalid parameter: ChartType", true);
    }

    // get file name w.r.t. alias and as well as validate wrt version
    var luVal = _FCD.R.chartAlias[cTyp][ _FCD.R.chartAliasMeta[cVer][0] ];
    if( typeof(luVal)=='number' )
    {
        if(luVal == -1) {
            _FCD.notify(_FCD.R.errorAlias + "\nDebug Info: ChartType \""
                + cTyp + "\" is not supported in ChartVersion \""
                + cVer + "\"", true);
        }
        luVal = _FCD.R.chartAlias[cTyp][ luVal ];
    }

    return _FCD.R.chartAliasMeta[cVer][1] + luVal +
    _FCD.R.chartAliasMeta[cVer][2]; // return after adding prefix and suffix

};

/*
 * This function calls "alert" only if it is enabled in settings
 * @param: msg string "Alert message"
 * @param: thrw bool "Whether to throw error or not"
 */
_FCD.notify = function(msg, thrw)
{
    var $1 = _FCD.Settings.debugmode.toString(),
        $2 = _FCD.Settings.parameters.debugmode;
    if($2) $2=_FCD.Settings.parameters.debugmode.toString();
    
    if( $1 == 'true' || $1 == '1' || $2 == 'true' || $2 == '1') alert(msg);
    if(thrw) throw msg;
};


// =============================================================================
// == Library and Resources ====================================================
// =============================================================================

/* String resources; Separated from JS for language localization and js
 * compression.
 */
_FCD.R =
{
    // tagName for <tag> support
    fcTag : 'fusioncharts',

    // message to show in case of unexpected error
    errorUnexpected: 'FusionCharts Error: An unexpected error had occured '+
    'while creating chart.\nDebug Information: ',

    // error message when there is an error with charttype alias
    errorAlias: 'FusionCharts Error: There was an error processing Chart Alias',

    // error message when no valid datasource is found
    errorNoValidData: 'FusionCharts Error: Could not parse a valid data source.',

    // error raised when settings could not be parsed due to user error
    errorParseSettings: 'FusionCharts Error: Could not parse script settings.',

    errorNoChartId: 'FusionCharts Parameter Error: Absence of ChartId invalidates '+
    '"autoChartId=true" settings.',

    errorInline: 'FusionCharts: Unable to parse inline data. All charts with' +
        'inline data will fail to render.',
    
    // the element type in which new charts will be rendered
    containerTagName: 'span',

    // prefix for every new chartid (in acse auto id is enabled)
    idPrefix: 'fusioncharts_',

    // file name array of this script
    jsFileName: ['fusionchartsdom.js', 'fusionchartsdom.debug.js'],

    // the prefix to be used in settings and other attributes
    userSettingsTagPrefix: '',

    // these attributes will not be parsed and added to the swfobjects addparam
    // method (regexp)
    reservedAttributes: ['style', 'class', 'id', 'name', 'title', 'on.+',
    'value', 'src', 'runat', 'spry:.+'],
    // other values: 'href', 'rel', 'dir', 'lang', 'align' , 'font', 'color', 'border', 'type',
    // 'size', 'alt', 'valign'

    chartVariables: ['XMLLoadingText',
        'ParsingDataText',
        'ChartNoDataText',
        'RenderingChartText',
        'LoadDataErrorText',
        'InvalidXMLText',
        'PBarLoadingText'],

    //  meta information for chart alias table
    // product_name: [acess index for Alias, prefix, suffix]
    chartAliasMeta: {
        v3:   [0, '',     '.swf'],
        free: [1, 'FCF_', '.swf']
    },

    // charttypes that are not charts
    chartExclusionMeta: {
        exportcomponent: true
    },

    // Chart Alias MATRIX. This method is preferred over a more efficient heuristics deviation table
    // because of keeping the lookup tables 'simpler to maintain' for developers unable to understand the
    // more complex (though more easier to compile] table.
    // some reference: 0 (any number above 0) stands for refering to the corresponding index for names
    // 				  -1 means not available in current version
    chartAlias: {
        dragcolumn2d:		['DragColumn2D',		-1],
        dragline:			['DragLine',			-1],
        dragarea:			['DragArea',			-1],
        errorbar2D:			['ErrorBar2D',			-1],
        selectscatter:		['SelectScatter',		-1],
        dragnode:			['DragNode',		    -1],
        kagi:				['Kagi',	-			 1],
        logcolumn2d:		['LogMSColumn2D', 		-1],
        logline2d:			['LogMSLine', 	  		-1],
        multilevelpie:		['MultiLevelPie'  		-1],
        multiaxisline:		['MultiAxisLine',  		-1],
        radar:				['Radar',  				-1],
        funnel:				['Funnel', 		 		0],
        candlestick:		['Candlestick',	 		0],
        gantt:				['Gantt',		 		0],
        spline2d:			['Spline',  			-1],
        msspline2d:			['MSSpline',  			-1],
        splinearea2d:		['SplineArea',  		-1],
        mssplinearea2d:		['MSSplineArea',  		-1],
        inversearea2d:		['InverseMSArea',  		-1],
        inversecolumn2d:	['InverseMSColumn2D',  	-1],
        inverseline2d:		['InverseMSLine',		-1],
        waterfall:			['Waterfall2D',			-1],
        scatter:			['Scatter',				-1],
        bubble:				['Bubble',				-1],
        column3d:			['Column3D',			0],
        column2d:			['Column2D',			0],
        mscolumn3d:			['MSColumn3D',			0],
        mscolumn2d:			['MSColumn2D',			0],
        stackedbar2d:		['StackedBar2D',		0],
        stackedcolumn3d:	['StackedColumn3D',		0],
        stackedcolumn2d:	['StackedColumn2D',		0],
        stackedbar3d:		['StackedBar3D',  		-1],
        stackedarea2d:		['StackedArea2D',		0],
        stackedcolumn3dlinedy:  ['StackedColumn3DLineDY',   -1], // the alias name is an exception of not having suffix 3d
        pie2d:				['Pie2D',				0],
        pie3d:				['Pie3D',				0],
        doughnut2d:			['Doughnut2D',			0],
        donut2d: ['Doughnut2D', 0],
        doughnut3d:			['Doughnut3D',			-1],
        donut3d: ['Doughnut3D', -1],
        line2d:				['Line',				0],
        msline2d:			['MSLine',				0],
        bar2d:				['Bar2D',				0],
        msbar2d:			['MSBar2D',				0],
        msbar3d:			['MSBar3D',				-1],
        area2d:				['Area2D',				0],
        msarea2d:			['MSArea',				'MSArea2D'],
        mscombi2d:			['MSCombi2D',			-1],
        mscombi3d:			['MSCombi3D',			-1],
        mscombidy2d:		['MSCombiDY2D',			'MSColumn2DLineDY'],
        msstackedcolumn2d:  ['MSStackedColumn2D', -1],
        msstackedcolumn2dlinedy:['MSStackedColumn2DLineDY', -1],
        mscolumn3dlinedy:	['MSColumn3DLineDY',	0],
        mscolumn3dline:		['MSColumnLine3D',		-1],
        scrollarea2d:		['ScrollArea2D',		-1],
        scrollcolumn2d:		['ScrollColumn2D',		-1],
        scrollline2d:		['ScrollLine2D',		-1],
        scrollcombi2d:		['ScrollCombi2D',		-1],
        scrollcombidy2d:	['ScrollCombiDY2D',		-1],
        scrollstackedcolumn2d: ['ScrollStackedColumn2D', -1],
        realtimearea:       ['RealTimeArea', -1],
        realtimecolumn:     ['RealTimeColumn',      -1],
        realtimeline:       ['RealTimeLine',        -1],
        realtimestackedarea:    ['RealTimeStackedArea',     -1],
        realtimestackedcolumn:  ['RealTimeStackedColumn',   -1],
        realtimeangular:  	['AngularGauge',        -1],
        realtimebulb:       ['Bulb',                -1],
        realtimecylinder: 	['Cylinder',            -1],
        realtimehorizontalled:  ['HLED',            -1],
        realtimehorizontallinear:   ['HLinearGauge',    -1],
        realtimethermometer: 	['Thermometer',     -1],
        realtimeverticalled: 	['VLED',            -1],
        sparkline:          ['SparkLine',           -1],
        sparkcolumn:        ['SparkColumn',         -1],
        sparkwinloss:       ['SparkWinLoss',        -1],
        horizontalbullet:  	['HBullet',             -1],
        verticalbullet:     ['VBullet',             -1],
        pyramid:            ['Pyramid',             -1],
        drawingpad:         ['DrawingPad',          -1],
        exportcomponent:    ['FCExporter',          -1]
    }
};

/*
 * Common runtime/library snippet functions that will be repeatedly and extensively
 * used in this script. All cross-browser functions should be implemented hers,
 * so that debugging is faster.
 */
_FCD.L =
{
    d: document,
    w: window,
    isIE: navigator.appName == "Microsoft Internet Explorer",
    isFF: document.getElementById && !document.all,

    // attach an event to the window
    attachEvent: function(obj, eventName, func)
    {
        return (_FCD.L.isIE) ? obj.attachEvent('on'+eventName, func) :
        obj.addEventListener(eventName, func, false);
    },

    get: function(id, obj)
    {
        return (obj || _FCD.L.d).getElementById(id);
    },

    // implement getElementsByTagName
    tags: function(nm, obj)
    {
        return (obj || _FCD.L.d).getElementsByTagName(nm);
    },

    // put getAttribute here as its not browser safe
    getAttribute: function(obj, attr)
    {
        return obj.getAttribute(attr);
    },

    // put setAttribute here as its not browser safe
    setAttribute: function(obj, attr, val)
    {
        return obj.setAttribute(attr, val);
    },

    // returns an unique id
    luid: 0,
    uniqueId: function()
    {
        return ++_FCD.L.luid;
    },

    // get new dom node
    getNew: function(tag)
    {
        var el = _FCD.L.d.createElement(tag), arg;
        for(var c=1; c<arguments.length; c++)
        {
            arg = arguments[c].split('=');
            el.setAttribute(arg[0], arg[1]);
        }
        return el;
    }

};

// fire the init() function upon page load
_FCD.L.attachEvent( _FCD.L.w, 'load', _FCD.Constructor);