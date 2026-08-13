document.getElementById('year').textContent = new Date().getFullYear() + 543;

  // ---- Crank–slider kinematic simulation for the hero engine diagram ----
  (function(){
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var piston   = document.getElementById('piston');
    var rod      = document.getElementById('rod');
    var crankpin = document.getElementById('crankpin');
    var spokes   = document.getElementById('flywheel-spokes');

    var cx = 200, cy = 340;   // crank center
    var r  = 55;              // crank radius
    var L  = 170;             // connecting rod length
    var pistonH = 46;
    var theta = 0;

    function frame(){
      theta += reduceMotion ? 0 : 0.018;

      var pinX = cx + r * Math.sin(theta);
      var pinY = cy - r * Math.cos(theta);

      var wristY = cy - ( r * Math.cos(theta) + Math.sqrt(L*L - Math.pow(r*Math.sin(theta),2)) );

      piston.setAttribute('transform', 'translate(0,' + (wristY - 138) + ')');
      rod.setAttribute('x1', 200);
      rod.setAttribute('y1', wristY + pistonH);
      rod.setAttribute('x2', pinX);
      rod.setAttribute('y2', pinY);

      crankpin.setAttribute('cx', pinX);
      crankpin.setAttribute('cy', pinY);

      spokes.setAttribute('transform', 'rotate(' + (theta * 180/Math.PI) + ' ' + cx + ' ' + cy + ')');

      if(!reduceMotion) requestAnimationFrame(frame);
    }
    frame();
  })();
