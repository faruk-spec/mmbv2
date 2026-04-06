/*!
 * Minimal QR Code Generator - Pure JavaScript
 * Supports Byte mode, versions 1-10, all error correction levels
 * No external dependencies
 * License: MIT
 */
(function(global){
'use strict';

// GF(256) tables with primitive polynomial 0x011D
var gfExp = new Uint8Array(512);
var gfLog = new Uint8Array(256);
(function(){
    var x = 1;
    for (var i = 0; i < 255; i++) {
        gfExp[i] = x;
        gfLog[x] = i;
        x <<= 1;
        if (x & 0x100) x ^= 0x11D;
    }
    for (var i = 255; i < 512; i++) gfExp[i] = gfExp[i - 255];
})();

function gfMul(a, b) {
    if (a === 0 || b === 0) return 0;
    return gfExp[(gfLog[a] + gfLog[b]) % 255];
}
function gfPow(x, p) { return gfExp[(gfLog[x] * p) % 255]; }

function rsGeneratorPoly(degree) {
    var g = [1];
    for (var i = 0; i < degree; i++) {
        var root = gfPow(2, i);
        var ng = new Array(g.length + 1).fill(0);
        for (var j = 0; j < g.length; j++) {
            ng[j] ^= gfMul(g[j], root);
            ng[j+1] ^= g[j];
        }
        g = ng;
    }
    return g.slice().reverse();
}

function rsEncode(data, numEC) {
    var gen = rsGeneratorPoly(numEC);
    var msg = data.concat(new Array(numEC).fill(0));
    for (var i = 0; i < data.length; i++) {
        var coef = msg[i];
        if (coef !== 0) {
            for (var j = 0; j < gen.length; j++) {
                msg[i + j] ^= gfMul(gen[j], coef);
            }
        }
    }
    return msg.slice(data.length);
}

// Capacity tables: [L,M,Q,H] data codewords per version
var CAP = [
    null,
    [19,16,13,9],[34,28,22,16],[55,44,34,26],[80,64,48,36],
    [108,86,62,46],[136,108,76,60],[156,124,88,66],[194,154,110,86],
    [232,182,132,100],[274,216,154,122]
];
// EC codewords per block per version [L,M,Q,H]
var EC_PER_BLOCK = [
    null,
    [7,10,13,17],[10,16,22,28],[15,26,18,22],[20,18,26,16],
    [26,24,18,22],[18,16,24,28],[20,18,18,26],[24,22,22,26],
    [30,22,20,24],[18,26,24,28]
];
// Blocks per version [L,M,Q,H]
var BLOCKS = [
    null,
    [1,1,1,1],[1,1,1,1],[1,1,2,2],[1,2,2,4],
    [1,2,4,4],[2,4,4,4],[2,4,2,5],[2,2,4,6],
    [2,3,4,7],[4,4,6,7]
];
var EC_LEVEL_BITS = {L:0x01,M:0x00,Q:0x03,H:0x02};

function getVersion(dataLen, ecLevel) {
    var ecIdx = {L:0,M:1,Q:2,H:3}[ecLevel];
    for (var v = 1; v <= 10; v++) {
        if (CAP[v][ecIdx] >= dataLen + 2) return v;
    }
    return 10;
}

function QRMatrix(ver) {
    var size = 21 + (ver - 1) * 4;
    var mat = [];
    var used = [];
    for (var i = 0; i < size; i++) {
        mat.push(new Uint8Array(size));
        used.push(new Uint8Array(size));
    }
    return {mat:mat, used:used, size:size};
}

function setModule(qr, r, c, v) {
    if (r < 0 || c < 0 || r >= qr.size || c >= qr.size) return;
    qr.mat[r][c] = v ? 1 : 0;
    qr.used[r][c] = 1;
}

function addFinder(qr, r, c) {
    var p = [[1,1,1,1,1,1,1],[1,0,0,0,0,0,1],[1,0,1,1,1,0,1],[1,0,1,1,1,0,1],[1,0,1,1,1,0,1],[1,0,0,0,0,0,1],[1,1,1,1,1,1,1]];
    for (var i = 0; i < 7; i++) for (var j = 0; j < 7; j++) setModule(qr, r+i, c+j, p[i][j]);
    // separator
    for (var i = -1; i <= 7; i++) {
        setModule(qr, r-1, c+i, 0); setModule(qr, r+7, c+i, 0);
        setModule(qr, r+i, c-1, 0); setModule(qr, r+i, c+7, 0);
    }
}

function addTiming(qr) {
    for (var i = 8; i < qr.size - 8; i++) {
        var v = (i % 2 === 0) ? 1 : 0;
        setModule(qr, 6, i, v);
        setModule(qr, i, 6, v);
    }
}

function addAlignment(qr, ver) {
    var coords = [[],[],[6,18],[6,22],[6,26],[6,30],[6,34],[6,22,38],[6,24,42],[6,26,46],[6,28,50]];
    var c = coords[ver];
    for (var i = 0; i < c.length; i++) {
        for (var j = 0; j < c.length; j++) {
            var r2 = c[i], c2 = c[j];
            if (qr.used[r2][c2]) continue;
            for (var dr = -2; dr <= 2; dr++) for (var dc = -2; dc <= 2; dc++) {
                var v = (Math.abs(dr) === 2 || Math.abs(dc) === 2) ? 1 : (dr === 0 && dc === 0 ? 1 : 0);
                setModule(qr, r2+dr, c2+dc, v);
            }
        }
    }
}

function addDarkModule(qr, ver) {
    setModule(qr, 4*ver+9, 8, 1);
}

function reserveFormatArea(qr) {
    var s = qr.size;
    for (var i = 0; i < 9; i++) { qr.used[8][i]=1; qr.used[i][8]=1; }
    for (var i = s-8; i < s; i++) { qr.used[8][i]=1; qr.used[i][8]=1; }
}

function placeData(qr, bits) {
    var idx = 0, s = qr.size;
    var dir = -1, row = s - 1;
    for (var col = s - 1; col > 0; col -= 2) {
        if (col === 6) col--;
        for (var cnt = 0; cnt < s; cnt++) {
            var r = (dir === -1) ? (row - cnt) : cnt;
            for (var dc = 0; dc < 2; dc++) {
                var c = col - dc;
                if (!qr.used[r][c]) {
                    qr.mat[r][c] = (idx < bits.length) ? bits[idx++] : 0;
                }
            }
        }
        dir *= -1;
    }
}

function applyMask(qr, mask) {
    var s = qr.size;
    var fns = [
        function(r,c){return (r+c)%2===0;},
        function(r,c){return r%2===0;},
        function(r,c){return c%3===0;},
        function(r,c){return (r+c)%3===0;},
        function(r,c){return (Math.floor(r/2)+Math.floor(c/3))%2===0;},
        function(r,c){return (r*c)%2+(r*c)%3===0;},
        function(r,c){return ((r*c)%2+(r*c)%3)%2===0;},
        function(r,c){return ((r+c)%2+(r*c)%3)%2===0;}
    ];
    for (var r = 0; r < s; r++) {
        for (var c = 0; c < s; c++) {
            if (!qr.used[r][c] && fns[mask](r,c)) qr.mat[r][c] ^= 1;
        }
    }
}

function writeFormatInfo(qr, ecLevel, mask) {
    var data = (EC_LEVEL_BITS[ecLevel] << 3) | mask;
    var d = data << 10;
    var poly = 0x537;
    for (var i = 14; i >= 10; i--) {
        if (d & (1 << i)) d ^= (poly << (i - 10));
    }
    var fmt = ((data << 10) | d) ^ 0x5412;
    var s = qr.size;
    var places = [
        [8,0],[8,1],[8,2],[8,3],[8,4],[8,5],[8,7],[8,8],
        [7,8],[5,8],[4,8],[3,8],[2,8],[1,8],[0,8]
    ];
    for (var i = 0; i < 15; i++) {
        var bit = (fmt >> i) & 1;
        qr.mat[places[i][0]][places[i][1]] = bit;
        if (i < 7) {
            qr.mat[s-1-i][8] = bit;
        } else {
            qr.mat[8][s-8+(i-7)] = bit;
        }
    }
}

function score(qr) {
    var s = qr.size, tot = 0, m = qr.mat;
    // Rule 1: runs of 5+ same colour
    for (var r = 0; r < s; r++) {
        var run = 1;
        for (var c = 1; c < s; c++) {
            if (m[r][c] === m[r][c-1]) { run++; if (run === 5) tot+=3; else if (run>5) tot++; }
            else run = 1;
        }
    }
    for (var c = 0; c < s; c++) {
        var run = 1;
        for (var r = 1; r < s; r++) {
            if (m[r][c] === m[r-1][c]) { run++; if (run === 5) tot+=3; else if (run>5) tot++; }
            else run = 1;
        }
    }
    // Rule 2: 2x2 blocks
    for (var r = 0; r < s-1; r++) for (var c = 0; c < s-1; c++) {
        var v = m[r][c]; if (v===m[r][c+1]&&v===m[r+1][c]&&v===m[r+1][c+1]) tot+=3;
    }
    return tot;
}

function selectMask(qr) {
    var best = -1, bestScore = Infinity;
    for (var m = 0; m < 8; m++) {
        applyMask(qr, m);
        var s = score(qr);
        if (s < bestScore) { bestScore = s; best = m; }
        applyMask(qr, m); // unapply
    }
    return best;
}

function QRCode(text, ecLevel) {
    ecLevel = ecLevel || 'M';
    var ecIdx = {L:0,M:1,Q:2,H:3}[ecLevel];
    
    // Encode as byte array
    var bytes = [];
    for (var i = 0; i < text.length; i++) {
        var c = text.charCodeAt(i);
        if (c < 0x80) { bytes.push(c); }
        else if (c < 0x800) { bytes.push((c>>6)|0xC0, (c&0x3F)|0x80); }
        else { bytes.push((c>>12)|0xE0, ((c>>6)&0x3F)|0x80, (c&0x3F)|0x80); }
    }
    
    var ver = getVersion(bytes.length, ecLevel);
    var cap = CAP[ver][ecIdx];
    
    // Build data codewords
    var bits = [];
    // Mode indicator: byte = 0100
    bits.push(0,1,0,0);
    // Character count (8 bits for v1-9)
    for (var i = 7; i >= 0; i--) bits.push((bytes.length >> i) & 1);
    // Data bytes
    for (var i = 0; i < bytes.length; i++) {
        for (var j = 7; j >= 0; j--) bits.push((bytes[i] >> j) & 1);
    }
    // Terminator
    for (var i = 0; i < 4 && bits.length < cap * 8; i++) bits.push(0);
    // Pad to byte boundary
    while (bits.length % 8) bits.push(0);
    // Pad bytes
    var pads = [0xEC, 0x11];
    var pi = 0;
    while (bits.length < cap * 8) {
        var p = pads[pi++ % 2];
        for (var j = 7; j >= 0; j--) bits.push((p >> j) & 1);
    }
    
    // Data codewords
    var codewords = [];
    for (var i = 0; i < cap; i++) {
        var v = 0;
        for (var j = 0; j < 8; j++) v = (v << 1) | bits[i*8+j];
        codewords.push(v);
    }
    
    // Reed-Solomon
    var numBlocks = BLOCKS[ver][ecIdx];
    var ecPerBlock = EC_PER_BLOCK[ver][ecIdx];
    var blockSize = Math.floor(cap / numBlocks);
    var extra = cap % numBlocks;
    
    var dataBlocks = [], ecBlocks = [];
    var pos = 0;
    for (var b = 0; b < numBlocks; b++) {
        var blen = blockSize + (b >= numBlocks - extra ? 1 : 0);
        var block = codewords.slice(pos, pos + blen);
        pos += blen;
        dataBlocks.push(block);
        ecBlocks.push(rsEncode(block, ecPerBlock));
    }
    
    // Interleave
    var finalCW = [];
    var maxD = Math.max.apply(null, dataBlocks.map(function(b){return b.length;}));
    for (var i = 0; i < maxD; i++) {
        for (var b = 0; b < numBlocks; b++) {
            if (i < dataBlocks[b].length) finalCW.push(dataBlocks[b][i]);
        }
    }
    for (var i = 0; i < ecPerBlock; i++) {
        for (var b = 0; b < numBlocks; b++) finalCW.push(ecBlocks[b][i]);
    }
    
    // Convert to bits
    var finalBits = [];
    for (var i = 0; i < finalCW.length; i++) {
        for (var j = 7; j >= 0; j--) finalBits.push((finalCW[i] >> j) & 1);
    }
    // Remainder bits
    var rem = [0,0,7,7,7,7,7,0,0,0,0][ver];
    for (var i = 0; i < rem; i++) finalBits.push(0);
    
    // Build matrix
    var qr = QRMatrix(ver);
    addFinder(qr, 0, 0);
    addFinder(qr, 0, qr.size - 7);
    addFinder(qr, qr.size - 7, 0);
    addTiming(qr);
    if (ver > 1) addAlignment(qr, ver);
    addDarkModule(qr, ver);
    reserveFormatArea(qr);
    placeData(qr, finalBits);
    
    var mask = selectMask(qr);
    applyMask(qr, mask);
    writeFormatInfo(qr, ecLevel, mask);
    
    this.size = qr.size;
    this.modules = qr.mat;
}

QRCode.prototype.toCanvas = function(canvas, opts) {
    opts = opts || {};
    var moduleSize = opts.moduleSize || 4;
    var quiet = opts.quiet !== undefined ? opts.quiet : 4;
    var light = opts.light || '#ffffff';
    var dark = opts.dark || '#000000';
    var totalSize = (this.size + quiet * 2) * moduleSize;
    canvas.width = totalSize;
    canvas.height = totalSize;
    var ctx = canvas.getContext('2d');
    ctx.fillStyle = light;
    ctx.fillRect(0, 0, totalSize, totalSize);
    ctx.fillStyle = dark;
    for (var r = 0; r < this.size; r++) {
        for (var c = 0; c < this.size; c++) {
            if (this.modules[r][c]) {
                ctx.fillRect((quiet + c) * moduleSize, (quiet + r) * moduleSize, moduleSize, moduleSize);
            }
        }
    }
};

QRCode.prototype.toDataURL = function(opts) {
    var canvas = document.createElement('canvas');
    this.toCanvas(canvas, opts);
    return canvas.toDataURL('image/png');
};

// Expose
if (typeof module !== 'undefined' && module.exports) {
    module.exports = QRCode;
} else {
    global.QRCode = QRCode;
}
})(typeof window !== 'undefined' ? window : this);
