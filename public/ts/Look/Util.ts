
// JQuery lib
var class2type : any[any] = {};
var getProto   = Object.getPrototypeOf;
var toString   = class2type.toString;
var hasOwn     = class2type.hasOwnProperty;
var fnToString = hasOwn.toString;
var ObjectFunctionString = fnToString.call(Object);
var isArray = Array.isArray;

export { isArray, ObjectFunctionString, fnToString, hasOwn, toString }

export function isUndefined(obj : any) : boolean { return typeof obj === "undefined" }

// Support: Chrome <=57, Firefox <=52
// In some browsers, typeof returns "function" for HTML <object> elements
// (i.e., `typeof document.createElement( "object" ) === "function"`).
// We don't want to classify *any* DOM node as a function.
export function isFunction(obj: any) : boolean { return typeof obj === "function" && typeof obj.nodeType !== "number"; }

export function getXhr() : any {
    try       { return new ActiveXObject("Msxml2.XMLHTTP"); }
    catch (e) { try { return new ActiveXObject("Microsoft.XMLHTTP"); } catch (E) {} }
    if (typeof XMLHttpRequest !== 'undefined') return new XMLHttpRequest();
    return false;
}

export function isPlainObject(obj: any) : boolean
{
    var proto, Ctor;
    if (!obj || toString.call(obj) !== "[object Object]") { return false; }
    proto = getProto(obj);
    if (!proto) { return true; }
    Ctor = hasOwn.call(proto, "constructor") && proto.constructor;
    return typeof Ctor === "function" && fnToString.call(Ctor) === ObjectFunctionString;
}

export function isWindow(obj: any) : boolean { return obj !== null && obj === obj.window; }

export function toType(obj: any) 
{
    if (obj === null) return obj + "";

    // Support: Android <=2.3 only (functionish RegExp)
    return typeof obj === "object" || typeof obj === "function" ?
            class2type[toString.call(obj)] || "object" :
            typeof obj;
}

export function isArrayLike(obj: any)
{
    // Support: real iOS 8.2 only (not reproducible in simulator)
    // `in` check used to prevent JIT error (gh-2145)
    // hasOwn isn't used here due to false negatives
    // regarding Nodelist length in IE
    var length = !!obj && "length" in obj && obj.length,
        type   = toType(obj);

    if (isFunction(obj) || isWindow(obj)) {
        return false;
    }

    return type === "array" || length === 0 || typeof length === "number" && length > 0 && ( length - 1 ) in obj;
}

/**
 * Each
 */
export function each(obj : any, callback: Function|null)
{
    if (isArrayLike(obj)) {

        var length = obj.length, i = 0;

        for (; i < length; i++)
            if (callback.call(obj[i], i, obj[i]) === false)
                break;
    }
    else {

        var prop : any;
        for (prop in obj)
            if (callback.call(obj[prop], prop, obj[prop]) === false)
                break;
    }

    return obj;
}

export function extend(this : any, ...args : any[])
{
    var options, name, src, copy, copyIsArray, clone,
        target = arguments[0] || {},
        i = 1,
        length = arguments.length,
        deep = false;

    // Handle a deep copy situation
    if (typeof target === "boolean") {
        deep = target;

        // Skip the boolean and the target
        target = arguments[i] || {};
        i++;
    }

    // Handle case when target is a string or something (possible in deep copy)
    if (typeof target !== "object" && !isFunction(target)) {
        target = {};
    }

    // Extend jQuery itself if only one argument is passed
    if (i === length) {
        target = this;
        i--;
    }

    for (; i < length; i++) {

        // Only deal with non-null/undefined values
        if ((options = arguments[i]) !== null) {

            // Extend the base object
            for(name in options) {

                src  = target[name];
                copy = options[name];

                if (target === copy) { continue; }

                if (deep && copy && (isPlainObject(copy) ||
                (copyIsArray = isArray(copy)))) {

                    if (copyIsArray) {
                        copyIsArray = false;
                        clone = src && isArray(src) ? src : [];
                    } else {
                        clone = src && isPlainObject(src) ? src : {};
                    }

                    target[name] = extend(deep, clone, copy);

                } else if (copy !== undefined) {
                    target[name] = copy;
                }
            }
        }
    }

    return target;
}

export function buildParams(prefix : string, obj : any, traditional : boolean, add : Function)
{    
    var name: any;

    if (isArray(obj)) {

        // Serialize array item.
        each(obj, function(i : any, v : any) {
            if (traditional || /\[\]$/.test(prefix)) {

                // Treat each array item as a scalar.
                add(prefix, v);

            } else {

                // Item is non-scalar (array or object), encode its numeric index.
                buildParams(
                    prefix + "[" + ( typeof v === "object" && v !== null ? i : "" ) + "]",
                    v,
                    traditional,
                    add
                );
            }
        });

    } else if (!traditional && toType(obj) === "object") {

        // Serialize object item.
        for (name in obj) {
            buildParams(prefix + "[" + name + "]", obj[name], traditional, add);
        }

    } else { add(prefix, obj); }
}

/**
 * Конвертирует объект в строку параметров
 */
export function param(a : any, traditional: boolean = false) : string
{
    var prefix : any,
        s : any[] = [],
        add = function(key: string, valueOrFunction: any) {

            // If value is a function, invoke it and use its return value
            var value = isFunction(valueOrFunction) ?
                valueOrFunction() :
                valueOrFunction;

            s[s.length] = encodeURIComponent(key) + "=" +
                        encodeURIComponent(value === null ? "" : value);
        };

    // If an array was passed in, assume that it is an array of form elements.
    if (isArray(a) || (a && a.jquery && !isPlainObject(a))) {

        // Serialize the form elements
        each(a, function(this : any) {
            add(this.name, this.value);
        });

    } else {

        // If traditional, encode the "old" way (the way 1.3.2 or older
        // did it), otherwise encode params recursively.
        for (prefix in a) {
            buildParams(prefix, a[prefix], traditional, add);
        }
    }

    return s.join("&");
};

export function map(elems : any, callback : any, arg : any = null) : any
{
    var length, value,
        i : any,
        ret = [];

    // Go through the array, translating each of the items to their new values
    if (isArrayLike(elems)) {
        length = elems.length;
        i = 0;
        for ( ; i < length; i++ ) {
            value = callback(elems[ i ], i, arg);

            if (value !== null) {
                ret.push(value);
            }
        }

    // Go through every key on the object,
    } else {
        for (i in elems) {
            value = callback(elems[i], i, arg);

            if (value !== null) {
                ret.push(value);
            }
        }
    }

    // Flatten any nested arrays
    return [].concat.apply([], ret);
}

export function getValue(elem: any) : any
{
    if (elem) {
        var ret = elem.value;

        // Handle most common string cases
        if (typeof ret === "string") {
            return ret.replace(/\r/g, "");
        }

        // Handle cases where value is null/undef or number
        return ret === null ? "" : ret;
    }
    return null;
}

export function setValue(elem : any, value : any) : void
{
    var valueIsFunction = isFunction(value), val : any, result: any;

    if (elem.nodeType !== 1) {
        return;
    }

    if (valueIsFunction) {
        val = value.call(elem, val(elem));
    } else {
        val = value;
    }

    // Treat null/undefined as ""; convert numbers to string
    if (val === null) {
        result = "";
    } else if (typeof val === "number") {
        result += val + "";
    } else if (Array.isArray(val)) {
        result = map(val, function(value : any) {
            return value === null ? "" : value + "";
        });
    }
    elem.value = result;
}

/*!
 * Serialize all form data into a query string
 * (c) 2018 Chris Ferdinandi, MIT License, https://gomakethings.com
 * @param  {Node}   form The form to serialize
 * @return {String}      The serialized form data
 */
export function serialize(form: any) {

    // Setup our serialized data
    var serialized : any[any] = {};

    // Loop through each field in the form
    for (var i = 0; i < form.elements.length; i++) {

        var field = form.elements[i];

        // Don't serialize fields without a name, submits, buttons, file and reset inputs, and disabled fields
        if (!field.name
        || field.disabled
        || field.type === 'file'
        || field.type === 'reset'
        || field.type === 'submit'
        || field.type === 'button') continue;

        // If a multi-select, get all selections
        if (field.type === 'select-multiple') {
            serialized[field.name] = [];
            for (var n = 0; n < field.options.length; n++) {
                if (!field.options[n].selected) continue;
                serialized[field.name].push(getValue(field.options[n].value));
            }
        }
        else if (field.type === 'checkbox') {
            serialized[field.name] = field.checked;
        }
        else if (field.type !== 'radio' || field.checked) {
            serialized[field.name] = getValue(field);
        }
    }

    return param(serialized);
}

/**
 * Возвращает корректное время в UnixTimeStamp
 */
export function getCurrectTimeStamp() : number {
    return Math.round((new Date()).getTime() / 1000);
}

/**
 * Hex to String
 */
export function hex2a(hexx: string) : string {
    var hex = hexx.toString();//force conversion
    var str = '';
    for (var i = 0; (i < hex.length && hex.substr(i, 2) !== '00'); i += 2)
        str += String.fromCharCode(parseInt(hex.substr(i, 2), 16));
    return str;
}

/**
 * String to hex
 */
export function a2hex(str: string) : string {
    var arr = [];
    for (var i = 0, l = str.length; i < l; i ++) {
        var hex = Number(str.charCodeAt(i)).toString(16);
        arr.push(hex);
    }
    return arr.join('');
}