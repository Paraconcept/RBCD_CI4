# Planche A4 de stickers pour le pavé numérique, à l'échelle 1:1 (unités en mm)
import sys
W, H, WIDE, TALL = 12, 14, 32, 34
P = 20  # pas entre deux touches (0 = 12 + 20, Entrée = 14 + 20)
NAVY, GREEN, RED, ORANGE, BLUE, GREY, TEAL, YELLOW, BAR = '#2f4a7a', '#16a34a', '#84252B', '#d97706', '#0284c7', '#64748b', '#0d9488', '#eab308', '#fde047'
keys = [  # (colonne, rangée, largeur, hauteur, couleur, symbole, libellé)
    (0, 0, W, H, BAR, 'glass', 'appel bar'),
    (1, 0, W, H, GREY, '/', 'effacer'), (2, 0, W, H, BLUE, '*', 'chrono'), (3, 0, W, H, TEAL, '⌫', 'menu'),
    (0, 1, W, H, NAVY, '7', ''), (1, 1, W, H, NAVY, '8', 'arrow:0'), (2, 1, W, H, NAVY, '9', ''), (3, 1, W, H, ORANGE, '−', 'corriger'),
    (0, 2, W, H, NAVY, '4', 'arrow:270'), (1, 2, W, H, NAVY, '5', ''), (2, 2, W, H, NAVY, '6', 'arrow:90'), (3, 2, W, H, GREEN, '+', '1 point'),
    (0, 3, W, H, NAVY, '1', ''), (1, 3, W, H, NAVY, '2', 'arrow:180'), (2, 3, W, H, NAVY, '3', ''), (3, 3, W, TALL, RED, '⏎', 'fin de<br>reprise'),
    (0, 4, WIDE, H, NAVY, '0', ''), (2, 4, W, H, YELLOW, '•', 'pause'),
]
SET_W, SET_H = 3 * P + W, 4 * P + H   # 72 × 94 mm

def sticker(x, y, w, h, color, sym, label):
    cls = 'k' + (' tall' if h == TALL else '')
    small = f'<small>{label}</small>' if label else ''
    if label.startswith('arrow:'):
        # Flèche de direction (8 haut, 2 bas, 4 gauche, 6 droite) pour les menus du marquoir
        small = (f'<span class="arr" style="transform:rotate({label[6:]}deg)"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">'
                 '<path d="M12 2 4 11h5v11h6V11h5z"/></svg></span>')
    if sym == '*':
        # L'astérisque de la police est petit et haut placé
        sym = '<span style="font-size:11mm;line-height:.75;transform:translateY(1.2mm);display:block">*</span>'
    if sym == 'glass':
        sym = '<span style="display:block;width:6.4mm;height:6.4mm"><svg style="width:100%;height:100%" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M5.5 2h13l-.5 6.3a6 6 0 0 1-11.9 0z"/><rect x="11" y="13.6" width="2" height="5.6"/><path d="M6.8 22c0-1.3 2.3-2.3 5.2-2.3s5.2 1 5.2 2.3z"/></svg></span>'
    ink = '#0a1020' if color in (YELLOW, BAR) else '#fff'
    return (f'<div class="{cls}" style="left:{x}mm;top:{y}mm;width:{w}mm;height:{h}mm;background:{color};color:{ink}">'
            f'<b>{sym}</b>{small}</div>')

def kit(ox, oy, n):
    html = [f'<div class="set-n" style="left:{ox}mm;top:{oy - 6}mm">Jeu {n}</div>']
    for c, r, w, h, color, sym, label in keys:
        html.append(sticker(ox + c * P, oy + r * P, w, h, color, sym, label))
    return ''.join(html)

ox1, ox2 = 19, 19 + SET_W + 28
oy1, oy2 = 48, 48 + SET_H + 22
sets = kit(ox1, oy1, 1) + kit(ox2, oy1, 2) + kit(ox1, oy2, 3) + kit(ox2, oy2, 4)

html = f'''<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow:wght@500;600&family=Barlow+Condensed:wght@700;800&display=swap">
<style>
@page {{ size: A4 portrait; margin: 0; }}
* {{ box-sizing: border-box; margin: 0; }}
body {{ width: 210mm; height: 297mm; position: relative; font-family: 'Barlow', sans-serif; color: #1b2233;
        -webkit-print-color-adjust: exact; print-color-adjust: exact; }}
h1 {{ position: absolute; left: 19mm; top: 12mm; font-family: 'Barlow Condensed'; font-weight: 800; font-size: 7mm; color: #0a1020; }}
.intro {{ position: absolute; left: 19mm; top: 21mm; width: 172mm; font-size: 3.2mm; line-height: 1.4; color: #5d677c; }}
.k {{ position: absolute; border-radius: 1.6mm; color: #fff; display: flex; flex-direction: column; align-items: center;
      justify-content: center; outline: .15mm dashed #b8bfcc; outline-offset: .6mm; }}
.k b {{ font-family: 'Barlow Condensed'; font-weight: 800; font-size: 7.2mm; line-height: 1; }}
.k small {{ font-family: 'Barlow'; font-weight: 600; font-size: 1.75mm; line-height: 1.05; margin-top: .5mm; text-align: center; }}
.k .arr {{ display: block; width: 3.4mm; height: 3.4mm; margin-top: .5mm; }}
.k .arr svg {{ width: 100%; height: 100%; display: block; }}
.k.tall b {{ font-size: 7.6mm; }}
.k.tall small {{ font-size: 1.9mm; margin-top: 1.2mm; }}
.set-n {{ position: absolute; font-size: 2.8mm; font-weight: 600; color: #8a93a6; }}
.foot {{ position: absolute; left: 19mm; top: 270mm; width: 172mm; font-size: 2.9mm; color: #5d677c; display: flex; align-items: center; gap: 6mm; }}
.ruler {{ width: 50mm; height: 3mm; border: .25mm solid #0a1020; border-top: none; flex: none; position: relative; }}
.ruler span {{ position: absolute; top: 3.6mm; left: 0; width: 50mm; text-align: center; font-size: 2.6mm; color: #0a1020; }}
</style></head><body>
<h1>RBC Disonais · Stickers du pavé numérique des marquoirs</h1>
<p class="intro">Échelle 1:1 — imprimer à <b>100 % / taille réelle</b> (pas « ajuster à la page »).
Touches carrées 12 × 14 mm · « 0 » 32 × 14 mm · « Entrée » 12 × 34 mm. Le pointillé est le repère de découpe (à 0,6 mm du bord).
Le sticker jaune au verre à pied se colle sur Num Lock (appel du bar). Les flèches de 8, 2, 4 et 6 servent à se déplacer dans les menus du marquoir. 4 jeux par planche : un pour le pavé, trois de rechange.</p>
{sets}
<div class="foot"><div class="ruler"><span>50 mm — vérifiez l'échelle avec une latte</span></div>
<div>Couleurs : vert #16a34a · bordeaux #84252B · orange #d97706 · bleu #0284c7 · gris #64748b · sarcelle #0d9488 · jaune #eab308 · jaune citron #fde047 · marine #2f4a7a</div></div>
</body></html>'''
open(sys.argv[1], 'w').write(html)
