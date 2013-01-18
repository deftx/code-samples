#include "Rave_Suit_main.h"

const uint8_t eqWidth = 8;
const uint8_t eqHeight = 5;
const uint8_t eqAverageBands = FFT_WIDTH / eqWidth;

byte curPixel = 0;

void equalizer()
{
  // Grid size
  static unsigned int eqMax[eqWidth] = { 0 };
  static unsigned int eqValueHistory[eqWidth] = { 0 };
  static unsigned long eqAmbienceAverage[eqWidth] = { 0 };
  static unsigned int eqAmbienceSamples[eqWidth] = { 0 };

  static byte r = 0;
  byte i, j;
  
  if (newPattern == true) {
    doAdcInit();
    clearStrip();
    useLedMap(chestOnlyBottom);
  }
  
  if (fftPosition == FFT_N)
  {
    fft_input(capture, bfly_buff);
    fft_execute(bfly_buff);
    fft_output(bfly_buff, spectrum);
    fftPosition = 0;
  
    // First bit of spectrum is very noisy.. 
    spectrum[0] = 0;
    
    for (j = 0; j < eqWidth; j++) {
      int eqValues[eqAverageBands];
      int eqValue;
            
      byte spectrumOffset = (j*eqAverageBands) - 1;

      for (i = 0; i < eqAverageBands; i++) {
        spectrumOffset += 1;
        
        eqValues[i] = spectrum[spectrumOffset];
      }
      
      // Get biggest value
      isort(eqValues, eqAverageBands);
      
      eqValue = eqValues[0];
      
	  // Decay slowly, rise quickly on each band
      if (eqValueHistory[j] < eqValue) {
        eqValue = (eqValue * 0.7) + (eqValueHistory[j] * 0.3);
      } else {
        eqValue = (eqValue * 0.4) + (eqValueHistory[j] * 0.6);
      }
      
      // Smooth out eqValue
      eqValue = smooth(eqValue, filterVal, eqValueHistory[j]);
      
      eqAmbienceAverage[j] += eqValue;
      eqAmbienceSamples[j]++;
            
      // Every 10 samples reset average
      if (eqAmbienceSamples[j] >= 10) {
        eqAmbienceSamples[j] = 1;
        eqAmbienceAverage[j] = eqValue;      
      }
     
      int ambience = (eqAmbienceAverage[j] / eqAmbienceSamples[j]) / 2;
      
      // Place current value in history
      eqValueHistory[j] = eqValue;
      
      
      eqMax[j] = max(eqValue, eqMax[j]);
      eqMax[j] *= 0.98;

      if (eqValue > ambience) {
        eqValue -= ambience;
        eqValue = min(eqHeight, int(0.5 + (eqValue / (eqMax[j] / eqHeight))));
      } else {
        eqValue = 1;
      }
           
      curPixel = 0;
      
      // For rainbow color
      if (r > 256) {
        r = 0;
      }
      
      // If rainbow mode is on, it will use this color
      uint32_t rColor = Wheel(r % 256);

      for (i = 0; i < eqHeight; i++) {
        curPixel += i == 0 ? j : eqWidth;
               
        if (i < eqValue) {
          if (setRainbow == true) {
            setColor = rColor;
          } else {
            if (i > 2) {
              setColor = Color(255,0,0);
            } else {
              setColor = Color(0,255,0);
            }
          }
        } else {
          setColor = Color(0,0,0);
        }
        
		// Set color on LED pixel
        strip.setPixelColor(curPixel, setColor);
      }
      
      r++;  
    }
  }
  
  strip.show();
}
