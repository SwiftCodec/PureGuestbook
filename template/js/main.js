document.onreadystatechange = function() {
  if (document.readyState === 'complete') {

    var pkgWdt=0, pkgHgt=0, pkgDpt=0;
    var rangeCosts = [1000, 8000, 27000];
    var costs = [2.99, 5.99, 10.99];
    var elPkgWidth = document.getElementById('numWidth');
    var elPkgHeight = document.getElementById('numHeight');
    var elPkgDepth = document.getElementById('numDepth');
    var elPkgVolume = document.getElementById('outVolume');
    var elPkgTotalCost = document.getElementById('totalCost');

    elPkgWidth.onchange = elPkgHeight.onchange = elPkgDepth.onchange = getPkgVolume;

    function getPkgVolume(event) {
    var pkgVol, pkgCost;
      switch(event.target.id) { 
        case 'numWidth':
          pkgWdt = event.target.value;
          break;
        case 'numHeight':
          pkgHgt = event.target.value;
          break;
        case 'numDepth':  
          pkgDpt = event.target.value;
          break;
        default:
          break;
      }
      pkgVol = pkgWdt * pkgHgt * pkgDpt;

      for (var i=0; i < rangeCosts.length; i++) {
        pkgCost = 0;
        if(pkgVol == 0) break;
        if(pkgVol <= rangeCosts[i]) {
          pkgCost = costs[i];
          break;
        }
      }

      elPkgVolume.innerHTML =  pkgVol>rangeCosts[rangeCosts.length-1] && '0 cm<sup>3</sup>' || pkgVol  + ' cm<sup>3</sup>';
      elPkgTotalCost.innerHTML = 'Total cost: ' + pkgCost + ' &euro;';


      console.log(event);
    }

  }
}

