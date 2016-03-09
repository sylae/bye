// mutilated socket.io tutorial. No idea what I'm doing
var http = require('http').Server();
var io = require('socket.io')(http);
var serialport = require("serialport");

http.listen(3000, function () { // @TODO: use config
  console.log('listening on *:3000');
});

var sp = new serialport.SerialPort("COM3", {// @TODO: use config
  parser: serialport.parsers.readline("\n")
});

sp.on("open", function () {
  console.log('open');
  sp.on('data', function (data) {
    io.emit("streamctrl", data);
    console.log('data received: ' + data);
  });
});
