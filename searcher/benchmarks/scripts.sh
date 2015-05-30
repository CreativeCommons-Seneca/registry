for F in ../3D_computer_graphics/*jpg; do ln -s "$F" .; done

for F in *jpg; do ../../phash-test/phash "$F" > "$F.txt"; done

for F in *jpg; do ../build/blockhash "$F" | awk -F' ' '{print $NF}' > "$F.txt"; done

ruby wimgs.rb --wiki commons.wikimedia.org --category "HDR_images" --images-dir ./HDR_images
