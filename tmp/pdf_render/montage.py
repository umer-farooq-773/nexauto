from PIL import Image, ImageOps, ImageDraw
import glob, os
fs=glob.glob('tmp/pdf_render/page-*.png')
ims=[]
for f in fs:
 im=Image.open(f).convert('RGB'); im.thumbnail((650,500)); im=ImageOps.expand(im,border=2,fill='gray'); ims.append((f,im))
out=Image.new('RGB',(1320,((len(ims)+1)//2)*530),'white'); d=ImageDraw.Draw(out)
for i,(f,im) in enumerate(ims):
 x=(i%2)*660; y=(i//2)*530; out.paste(im,(x,y+20)); d.text((x+5,y+3),os.path.basename(f),fill='black')
out.save('tmp/pdf_render/montage.png')
