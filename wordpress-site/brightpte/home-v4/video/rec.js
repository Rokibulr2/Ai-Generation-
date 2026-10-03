const { chromium } = require('playwright');
const { spawn } = require('child_process');
const FF=process.argv[2], OUT=process.argv[3], FPS=30, DUR=40;
(async()=>{
  const b=await chromium.launch();const p=await b.newPage({viewport:{width:1280,height:720}});
  await p.goto('file://'+__dirname+'/journey.html');await p.evaluate(()=>document.fonts.ready);
  const ff=spawn(FF,['-y','-f','image2pipe','-framerate',String(FPS),'-c:v','mjpeg','-i','-','-c:v','libx264','-preset','slow','-crf','24','-pix_fmt','yuv420p','-movflags','+faststart','-an',OUT],{stdio:['pipe','ignore','inherit']});
  const N=FPS*DUR;
  for(let f=0;f<N;f++){
    await p.evaluate(t=>render(t),f/FPS);
    const buf=await p.screenshot({type:'jpeg',quality:92});
    if(!ff.stdin.write(buf)) await new Promise(r=>ff.stdin.once('drain',r));
    if(f%300===0) process.stderr.write('frame '+f+'\n');
  }
  ff.stdin.end(); await new Promise(r=>ff.on('close',r));
  await p.evaluate(t=>render(t),16.8); await p.screenshot({path:__dirname+'/poster.jpg',type:'jpeg',quality:85});
  await b.close();
})();
