console.log(require('path').dirname(require.main.filename).replace('chart-udf/src', '') + '/.env');
require('dotenv').config({ path: require('path').dirname(require.main.filename).replace('chart-udf/src', '') + '/.env' })

let fs = require('fs');
let https;
let credentials;

if(process.env.TRADINGVIEW_CHART_SSL.toLowerCase() == "true") {
    https = require('https');
    let privateKey  = fs.readFileSync(process.env.TRADINGVIEW_CHART_PRIVATE_KEY, 'utf8');
    let certificate = fs.readFileSync(process.env.TRADINGVIEW_CHART_CERT_FILE, 'utf8');
    credentials = {key: privateKey, cert: certificate};
}

const express = require('express')
const app = express()

const cors = require('cors')
app.use(cors())

const morgan = require('morgan')
app.use(morgan('tiny'))

const UDF = require('./udf')
const udf = new UDF()

// Common

const query = require('./query')

function handlePromise(res, next, promise) {
    promise.then(result => {
        res.send(result)
    }).catch(err => {
        next(err)
    })
}

// Endpoints

app.all('/chart.io', (req, res) => {
    res.set('Content-Type', 'text/plain').send('Welcome to the Binance UDF Adapter for TradingView. See ./config for more details.')
})

app.get('/chart.io/time', (req, res) => {
    const time = Math.floor(Date.now() / 1000)  // In seconds
    res.set('Content-Type', 'text/plain').send(time.toString())
})

app.get('/chart.io/config', (req, res, next) => {
    handlePromise(res, next, udf.config())
})

app.get('/chart.io/symbol_info', (req, res, next) => {
    handlePromise(res, next, udf.symbolInfo())
})

app.get('/chart.io/symbols', [query.symbol], (req, res, next) => {
    handlePromise(res, next, udf.symbol(req.query.symbol))
})

app.get('/chart.io/search', [query.query, query.limit], (req, res, next) => {
    if (req.query.type === '') {
        req.query.type = null
    }
    if (req.query.exchange === '') {
        req.query.exchange = null
    }

    handlePromise(res, next, udf.search(
        req.query.query,
        req.query.type,
        req.query.exchange,
        req.query.limit
    ))
})

app.get('/chart.io/history', [
    query.symbol,
    query.from,
    query.to,
    query.resolution
], (req, res, next) => {
    handlePromise(res, next, udf.history(
        req.query.symbol,
        req.query.from,
        req.query.to,
        req.query.resolution
    ))
})

// Handle errors

app.use((err, req, res, next) => {
    if (err instanceof query.Error) {
        return res.status(err.status).send({
            s: 'error',
            errmsg: err.message
        })
    }

    if (err instanceof UDF.SymbolNotFound) {
        return res.status(404).send({
            s: 'error',
            errmsg: 'Symbol Not Found'
        })
    }
    if (err instanceof UDF.InvalidResolution) {
        return res.status(400).send({
            s: 'error',
            errmsg: 'Invalid Resolution'
        })
    }

    console.error(err)
    res.status(500).send({
        s: 'error',
        errmsg: 'Internal Error'
    })
})


//Listen


const port = process.env.TRADINGVIEW_CHART_PORT || 8081

if(process.env.TRADINGVIEW_CHART_SSL.toLowerCase() == "true") {
    var httpsServer = https.createServer(credentials, app);

    httpsServer.listen(port, () => {
        console.log(`Listening on port ${port}`)
    });
} else {
    app.listen(port, () => {
        console.log(`Listening on port ${port}`)
    })
}
