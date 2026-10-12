#!/usr/bin/env python3
"""Render WordPress.org listing images from the BoltUtil-owned logo artwork.

This script is for repository maintenance, not part of the installable plugin.
It needs Pillow and uses the exact alpha silhouette in brand-mark-source.png.
"""

from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


ROOT = Path(__file__).resolve().parent.parent
OUT = ROOT / "wordpress-org-assets"
SOURCE = OUT / "brand-mark-source.png"
RESAMPLE = Image.Resampling.LANCZOS


def font(size: int, bold: bool = False, chinese: bool = False) -> ImageFont.FreeTypeFont:
    if chinese:
        for candidate in ["/System/Library/Fonts/PingFang.ttc", "/System/Library/Fonts/STHeiti Medium.ttc", "/usr/share/fonts/opentype/noto/NotoSansCJK-Regular.ttc"]:
            if Path(candidate).is_file():
                return ImageFont.truetype(candidate, size)
        raise RuntimeError("A CJK font is required for the Chinese listing banner")
    choices = (
        ["/System/Library/Fonts/Supplemental/Arial Bold.ttf", "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf"]
        if bold
        else ["/System/Library/Fonts/Supplemental/Arial.ttf", "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf"]
    )
    for candidate in choices:
        if Path(candidate).is_file():
            return ImageFont.truetype(candidate, size)
    raise RuntimeError("Arial or DejaVu Sans font is required to build directory assets")


def bolt_mask(size: int) -> Image.Image:
    source = Image.open(SOURCE).convert("RGBA").getchannel("A")
    bbox = source.getbbox()
    if bbox is None:
        raise RuntimeError("The official BoltUtil mark has no visible pixels")
    return source.crop(bbox).resize((size, size), RESAMPLE)


def icon(size: int) -> Image.Image:
    scale = 4
    canvas = Image.new("RGBA", (size * scale, size * scale), (0, 0, 0, 0))
    draw = ImageDraw.Draw(canvas)
    draw.rounded_rectangle((0, 0, size * scale - 1, size * scale - 1), radius=round(size * scale * 0.23), fill="#07090f")
    mark_size = round(size * scale * 0.58)
    mask = bolt_mask(mark_size)
    offset = ((size * scale - mark_size) // 2, (size * scale - mark_size) // 2)
    canvas.paste("white", offset, mask)
    return canvas.resize((size, size), RESAMPLE)


def banner(chinese: bool = False) -> Image.Image:
    width, height = 1544, 500
    canvas = Image.new("RGB", (width, height), "#080d1b")
    draw = ImageDraw.Draw(canvas)
    draw.rounded_rectangle((58, 60, 1500, 440), radius=48, fill="#101a31")
    draw.rounded_rectangle((97, 126, 345, 374), radius=56, fill="#05070c")
    mask = bolt_mask(165)
    canvas.paste("white", (138, 168), mask)
    draw.text((405, 143), "BoltUtil", font=font(87, bold=True), fill="#ffffff")
    draw.text((1300, 96), "v0.6.1", font=font(29), fill="#b5bce2")
    subtitle = "USDT / USDC 稳定币收款 · WooCommerce" if chinese else "USDT / USDC payments for WooCommerce"
    detail = "六条链 · 钱包快捷支付或手动转账" if chinese else "6 networks · Wallet confirmation or manual transfer"
    draw.text((407, 248), subtitle, font=font(38, chinese=chinese), fill="#d8e3f6")
    draw.text((407, 319), detail, font=font(29, chinese=chinese), fill="#b5bce2")
    draw.rounded_rectangle((407, 387, 704, 395), radius=4, fill="#6d64ee")
    return canvas


def main() -> None:
    OUT.mkdir(exist_ok=True)
    for size in (128, 256):
        icon(size).save(OUT / f"icon-{size}x{size}.png", optimize=True)
    high = banner()
    high.save(OUT / "banner-1544x500.png", optimize=True)
    high.resize((772, 250), RESAMPLE).save(OUT / "banner-772x250.png", optimize=True)
    zh = banner(chinese=True)
    zh.save(OUT / "banner-1544x500-zh_CN.png", optimize=True)
    zh.resize((772, 250), RESAMPLE).save(OUT / "banner-772x250-zh_CN.png", optimize=True)


if __name__ == "__main__":
    main()
