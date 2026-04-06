/*!
 * QR Code Generator — Pure JavaScript
 * Byte mode, versions 1-10, all EC levels. No external dependencies.
 */
(function (global) {
'use strict';

/* ── GF(256) arithmetic ─────────────────────────────────────────────────── */
var EXP = [], LOG = [];
(function () {
    var x = 1;
    for (var i = 0; i < 255; i++) {
        EXP[i] = x;
        EXP[i + 255] = x;
        LOG[x] = i;
        x <<= 1;
        if (x >= 256) x ^= 0x11D; /* reduce mod x^8+x^4+x^3+x^2+1 */
    }
    EXP[510] = 1; /* guard: EXP is read up to index LOG[a]+LOG[b] <= 508 */
}());

function gmul(a, b) {
    return (a && b) ? EXP[LOG[a] + LOG[b]] : 0;
}

/* ── Reed-Solomon ────────────────────────────────────────────────────────── */
/*
 * Build the generator polynomial for `n` EC codewords.
 * g(x) = ∏_{i=0}^{n-1} (x + α^i)
 * Returned as coefficients [c_n-1, c_n-2, ..., c_1, c_0]
 * (index 0 = coefficient of x^(n-1), last index = constant term).
 */
function rsGenerator(n) {
    var g = [1];
    for (var i = 0; i < n; i++) {
        var ai = EXP[i];
        var ng = new Array(g.length + 1);
        for (var j = 0; j < ng.length; j++) ng[j] = 0;
        for (var j = 0; j < g.length; j++) {
            ng[j]     ^= g[j];
            ng[j + 1] ^= gmul(g[j], ai);
        }
        g = ng;
    }
    return g; /* g[0]=1 (leading), length=n+1 */
}

/*
 * Compute `n` Reed-Solomon error-correction codewords for `data`.
 * Uses an LFSR (shift-register) approach: avoids any polynomial-order
 * ambiguity and matches the QR spec directly.
 */
function rsEncode(data, n) {
    var gen = rsGenerator(n);
    /* rem[0..n-1] is the running remainder, initialised to 0 */
    var rem = [];
    for (var i = 0; i < n; i++) rem[i] = 0;

    for (var i = 0; i < data.length; i++) {
        var factor = data[i] ^ rem[0];
        /* shift left, XOR in generator */
        for (var j = 0; j < n - 1; j++) {
            rem[j] = rem[j + 1] ^ gmul(gen[j + 1], factor);
        }
        rem[n - 1] = gmul(gen[n], factor);
    }
    return rem;
}

/* ── Capacity / structure tables ─────────────────────────────────────────── */
/* Data codewords per version, indexed [version][ecLevel: L=0 M=1 Q=2 H=3] */
var DATA_CAP = [
    null,
    [19,16,13, 9], [34,28,22,16], [55,44,34,26], [80,64,48,36],
    [108,86,62,46],[136,108,76,60],[156,124,88,66],[194,154,110,86],
    [232,182,132,100],[274,216,154,122]
];
/* EC codewords per block */
var EC_PER_BLK = [
    null,
    [ 7,10,13,17],[10,16,22,28],[15,26,18,22],[20,18,26,16],
    [26,24,18,22],[18,16,24,28],[20,18,18,26],[24,22,22,26],
    [30,22,20,24],[18,26,24,28]
];
/* Number of blocks */
var NUM_BLKS = [
    null,
    [1,1,1,1],[1,1,1,1],[1,1,2,2],[1,2,2,4],
    [1,2,4,4],[2,4,4,4],[2,4,2,5],[2,2,4,6],
    [2,3,4,7],[4,4,6,7]
];
/* EC level indicator bits: L=01, M=00, Q=11, H=10 */
var EC_IND = { L: 1, M: 0, Q: 3, H: 2 };
/* Remainder bits appended after all codeword bits (v1..v10) */
var REM_BITS = [0, 0, 7, 7, 7, 7, 7, 0, 0, 0, 0];
/* Alignment pattern centre coordinates per version */
var ALIGN_COORD = [
    [], [], [6,18], [6,22], [6,26], [6,30], [6,34],
    [6,22,38], [6,24,42], [6,26,46], [6,28,50]
];

/* ── Version selection ────────────────────────────────────────────────────── */
function chooseVersion(byteCount, ecl) {
    var idx = { L:0, M:1, Q:2, H:3 }[ecl];
    for (var v = 1; v <= 10; v++) {
        /* need at least: 2 codewords overhead (mode+length) + data codewords */
        if (DATA_CAP[v][idx] >= byteCount + 2) return v;
    }
    return 10;
}

/* ── Matrix helpers ────────────────────────────────────────────────────────── */
function makeMatrix(n) {
    var m = [], f = [];
    for (var i = 0; i < n; i++) {
        m.push(new Array(n));
        f.push(new Array(n));
        for (var j = 0; j < n; j++) { m[i][j] = 0; f[i][j] = 0; }
    }
    return { m: m, f: f, n: n };
}

function put(qr, r, c, v) {
    if (r < 0 || c < 0 || r >= qr.n || c >= qr.n) return;
    qr.m[r][c] = v ? 1 : 0;
    qr.f[r][c] = 1;
}

/* ── Structural patterns ─────────────────────────────────────────────────── */
var FINDER = [
    1,1,1,1,1,1,1,
    1,0,0,0,0,0,1,
    1,0,1,1,1,0,1,
    1,0,1,1,1,0,1,
    1,0,1,1,1,0,1,
    1,0,0,0,0,0,1,
    1,1,1,1,1,1,1
];

function drawFinder(qr, r, c) {
    for (var i = 0; i < 7; i++)
        for (var j = 0; j < 7; j++)
            put(qr, r + i, c + j, FINDER[i * 7 + j]);
    /* white separator ring */
    for (var k = -1; k <= 7; k++) {
        put(qr, r - 1, c + k, 0);
        put(qr, r + 7, c + k, 0);
        put(qr, r + k, c - 1, 0);
        put(qr, r + k, c + 7, 0);
    }
}

function drawTiming(qr) {
    for (var i = 8; i < qr.n - 8; i++) {
        put(qr, 6, i, i % 2 === 0 ? 1 : 0);
        put(qr, i, 6, i % 2 === 0 ? 1 : 0);
    }
}

function drawAlignment(qr, ver) {
    var pos = ALIGN_COORD[ver];
    for (var a = 0; a < pos.length; a++) {
        for (var b = 0; b < pos.length; b++) {
            var cr = pos[a], cc = pos[b];
            if (qr.f[cr][cc]) continue; /* overlaps finder / timing */
            for (var dr = -2; dr <= 2; dr++) {
                for (var dc = -2; dc <= 2; dc++) {
                    var v = (Math.abs(dr) === 2 || Math.abs(dc) === 2) ? 1
                          : (dr === 0 && dc === 0) ? 1 : 0;
                    put(qr, cr + dr, cc + dc, v);
                }
            }
        }
    }
}

function drawDarkModule(qr, ver) {
    put(qr, 4 * ver + 9, 8, 1);
}

/* Mark format information areas as used (filled with real bits later) */
function reserveFormat(qr) {
    var n = qr.n;
    for (var i = 0; i < 9; i++) { qr.f[8][i] = 1; qr.f[i][8] = 1; }
    for (var i = n - 8; i < n; i++) { qr.f[8][i] = 1; qr.f[i][8] = 1; }
}

/* ── Data placement ─────────────────────────────────────────────────────── */
function placeData(qr, bits) {
    var n = qr.n, idx = 0, up = true;
    for (var col = n - 1; col > 0; col -= 2) {
        if (col === 6) col--; /* skip timing column */
        for (var row = 0; row < n; row++) {
            var r = up ? (n - 1 - row) : row;
            for (var dc = 0; dc < 2; dc++) {
                var c = col - dc;
                if (!qr.f[r][c]) {
                    qr.m[r][c] = idx < bits.length ? bits[idx++] : 0;
                }
            }
        }
        up = !up;
    }
}

/* ── Masking ─────────────────────────────────────────────────────────────── */
var MASK_FN = [
    function (r, c) { return (r + c) % 2 === 0; },
    function (r, c) { return r % 2 === 0; },
    function (r, c) { return c % 3 === 0; },
    function (r, c) { return (r + c) % 3 === 0; },
    function (r, c) { return (Math.floor(r / 2) + Math.floor(c / 3)) % 2 === 0; },
    function (r, c) { return (r * c) % 2 + (r * c) % 3 === 0; },
    function (r, c) { return ((r * c) % 2 + (r * c) % 3) % 2 === 0; },
    function (r, c) { return ((r + c) % 2 + (r * c) % 3) % 2 === 0; }
];

function applyMask(qr, mk) {
    var fn = MASK_FN[mk], n = qr.n;
    for (var r = 0; r < n; r++)
        for (var c = 0; c < n; c++)
            if (!qr.f[r][c] && fn(r, c)) qr.m[r][c] ^= 1;
}

function penaltyScore(qr) {
    var n = qr.n, m = qr.m, score = 0;
    /* Rule 1: runs of 5+ same colour in rows */
    for (var r = 0; r < n; r++) {
        var run = 1;
        for (var c = 1; c < n; c++) {
            if (m[r][c] === m[r][c - 1]) {
                run++;
                if (run === 5) score += 3;
                else if (run > 5) score++;
            } else { run = 1; }
        }
    }
    /* Rule 1: columns */
    for (var c = 0; c < n; c++) {
        var run = 1;
        for (var r = 1; r < n; r++) {
            if (m[r][c] === m[r - 1][c]) {
                run++;
                if (run === 5) score += 3;
                else if (run > 5) score++;
            } else { run = 1; }
        }
    }
    /* Rule 2: 2×2 same-colour blocks */
    for (var r = 0; r < n - 1; r++)
        for (var c = 0; c < n - 1; c++) {
            var v = m[r][c];
            if (v === m[r + 1][c] && v === m[r][c + 1] && v === m[r + 1][c + 1])
                score += 3;
        }
    return score;
}

function chooseMask(qr) {
    var best = 0, bestScore = Infinity;
    for (var mk = 0; mk < 8; mk++) {
        applyMask(qr, mk);
        var s = penaltyScore(qr);
        if (s < bestScore) { bestScore = s; best = mk; }
        applyMask(qr, mk); /* undo */
    }
    return best;
}

/* ── Format information ──────────────────────────────────────────────────── */
/*
 * BCH (15,5) with generator x^10+x^8+x^5+x^4+x^2+x+1 = 0x537.
 * XOR mask = 101010000010010 = 0x5412.
 *
 * Format bit positions (first copy, top-left corner), bit 0 = LSB:
 *   bit 0..5  → row 8, cols 0..5
 *   bit 6     → row 8, col 7   (col 6 is timing)
 *   bit 7     → row 8, col 8
 *   bit 8     → row 7, col 8
 *   bit 9     → row 5, col 8   (row 6 is timing)
 *   bit 10..14 → rows 4..0, col 8
 *
 * Second copy:
 *   bit 0..6  → col 8, rows size-1..size-7
 *   bit 7..14 → row 8, cols size-8..size-1
 */
var FMT_R1 = [8, 8, 8, 8, 8, 8, 8, 8, 7, 5, 4, 3, 2, 1, 0];
var FMT_C1 = [0, 1, 2, 3, 4, 5, 7, 8, 8, 8, 8, 8, 8, 8, 8];

function writeFormat(qr, ecl, mk) {
    /* 5-bit data word: [EC indicator (2 bits)][mask pattern (3 bits)] */
    var data = (EC_IND[ecl] << 3) | mk;
    /* Compute BCH remainder */
    var rem = data << 10;
    for (var i = 14; i >= 10; i--) {
        if (rem & (1 << i)) rem ^= 0x537 << (i - 10);
    }
    /* 15-bit format word (before XOR mask) */
    var fmt = ((data << 10) | (rem & 0x3FF)) ^ 0x5412;
    var n = qr.n;
    /* First copy (top-left) */
    for (var i = 0; i < 15; i++) {
        qr.m[FMT_R1[i]][FMT_C1[i]] = (fmt >> i) & 1;
    }
    /* Second copy: bits 0-6 → col 8 bottom-left; bits 7-14 → row 8 top-right */
    for (var i = 0; i < 7; i++)  qr.m[n - 1 - i][8]         = (fmt >> i) & 1;
    for (var i = 7; i < 15; i++) qr.m[8][n - 8 + (i - 7)]   = (fmt >> i) & 1;
}

/* ── Public constructor ──────────────────────────────────────────────────── */
function QRCode(text, ecLevel) {
    ecLevel = ecLevel || 'M';
    var ecIdx = { L:0, M:1, Q:2, H:3 }[ecLevel];

    /* UTF-8 encode */
    var bytes = [];
    for (var i = 0; i < text.length; i++) {
        var cp = text.charCodeAt(i);
        if (cp < 0x80) {
            bytes.push(cp);
        } else if (cp < 0x800) {
            bytes.push((cp >> 6) | 0xC0, (cp & 0x3F) | 0x80);
        } else {
            bytes.push((cp >> 12) | 0xE0, ((cp >> 6) & 0x3F) | 0x80, (cp & 0x3F) | 0x80);
        }
    }

    var ver = chooseVersion(bytes.length, ecLevel);
    var cap = DATA_CAP[ver][ecIdx];

    /* ── Build data bit stream ── */
    var bits = [];
    function pushBits(val, len) {
        for (var k = len - 1; k >= 0; k--) bits.push((val >> k) & 1);
    }
    pushBits(4, 4);              /* mode indicator: byte = 0100 */
    pushBits(bytes.length, 8);   /* character count (8 bits for v1-9 byte mode) */
    for (var i = 0; i < bytes.length; i++) pushBits(bytes[i], 8);
    for (var i = 0; i < 4 && bits.length < cap * 8; i++) bits.push(0); /* terminator */
    while (bits.length % 8) bits.push(0);                              /* byte-align */
    var pads = [0xEC, 0x11], pi = 0;
    while (bits.length < cap * 8) pushBits(pads[pi++ % 2], 8);        /* pad codewords */

    /* ── Convert bit stream to codeword array ── */
    var codewords = [];
    for (var i = 0; i < cap; i++) {
        var v = 0;
        for (var j = 0; j < 8; j++) v = (v << 1) | bits[i * 8 + j];
        codewords.push(v);
    }

    /* ── Reed-Solomon per block ── */
    var nb  = NUM_BLKS[ver][ecIdx];
    var ecp = EC_PER_BLK[ver][ecIdx];
    var bsz = Math.floor(cap / nb);
    var bex = cap % nb;

    var dBlocks = [], eBlocks = [], pos = 0;
    for (var b = 0; b < nb; b++) {
        var len = bsz + (b >= nb - bex ? 1 : 0);
        var blk = codewords.slice(pos, pos + len);
        pos += len;
        dBlocks.push(blk);
        eBlocks.push(rsEncode(blk, ecp));
    }

    /* ── Interleave data then EC codewords ── */
    var finalCW = [];
    var maxD = 0;
    for (var b = 0; b < nb; b++) if (dBlocks[b].length > maxD) maxD = dBlocks[b].length;
    for (var i = 0; i < maxD; i++)
        for (var b = 0; b < nb; b++)
            if (i < dBlocks[b].length) finalCW.push(dBlocks[b][i]);
    for (var i = 0; i < ecp; i++)
        for (var b = 0; b < nb; b++)
            finalCW.push(eBlocks[b][i]);

    /* ── Convert codewords to final bit stream + remainder bits ── */
    var finalBits = [];
    for (var i = 0; i < finalCW.length; i++) pushBits(finalCW[i], 8);
    for (var i = 0; i < REM_BITS[ver]; i++) finalBits.push(0);

    /* ── Build matrix ── */
    var qr = makeMatrix(21 + (ver - 1) * 4);
    drawFinder(qr, 0, 0);
    drawFinder(qr, 0, qr.n - 7);
    drawFinder(qr, qr.n - 7, 0);
    drawTiming(qr);
    if (ver > 1) drawAlignment(qr, ver);
    drawDarkModule(qr, ver);
    reserveFormat(qr);
    placeData(qr, finalBits);

    var mask = chooseMask(qr);
    applyMask(qr, mask);
    writeFormat(qr, ecLevel, mask);

    this.size    = qr.n;
    this.modules = qr.m;
}

/* ── Rendering ─────────────────────────────────────────────────────────── */
QRCode.prototype.toCanvas = function (canvas, opts) {
    opts = opts || {};
    var ms    = opts.moduleSize || 4;
    var quiet = opts.quiet !== undefined ? opts.quiet : 4;
    var dark  = opts.dark  || '#000000';
    var light = opts.light || '#ffffff';
    var total = (this.size + quiet * 2) * ms;
    canvas.width  = total;
    canvas.height = total;
    var ctx = canvas.getContext('2d');
    ctx.fillStyle = light;
    ctx.fillRect(0, 0, total, total);
    ctx.fillStyle = dark;
    for (var r = 0; r < this.size; r++) {
        for (var c = 0; c < this.size; c++) {
            if (this.modules[r][c]) {
                ctx.fillRect((quiet + c) * ms, (quiet + r) * ms, ms, ms);
            }
        }
    }
};

QRCode.prototype.toDataURL = function (opts) {
    var canvas = document.createElement('canvas');
    this.toCanvas(canvas, opts);
    return canvas.toDataURL('image/png');
};

/* ── Export ─────────────────────────────────────────────────────────────── */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = QRCode;
} else {
    global.QRCode = QRCode;
}
}(typeof window !== 'undefined' ? window : this));
