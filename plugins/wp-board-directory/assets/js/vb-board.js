/**
 * WP Board & Directory - Vanilla JS Core
 * Lightweight, zero dependencies (No jQuery required)
 */

document.addEventListener('DOMContentLoaded', function() {

    // 1. Voting & Ratings Handler (AJAX)
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.vb-btn-vote');
        if (!btn) return;

        e.preventDefault();
        var targetType = btn.getAttribute('data-target') || 'listing';
        var targetId   = btn.getAttribute('data-id');
        var voteVal    = btn.getAttribute('data-vote');
        var scoreSpan  = document.getElementById('vb-score-' + targetId);

        if (!targetId || !voteVal) return;

        btn.disabled = true;

        var params = new URLSearchParams({
            action: 'vb_submit_vote',
            nonce: (window.vbBoardData && window.vbBoardData.nonce) ? window.vbBoardData.nonce : '',
            target_type: targetType,
            target_id: targetId,
            rating: voteVal
        });

        var ajaxUrl = (window.vbBoardData && window.vbBoardData.ajaxUrl) ? window.vbBoardData.ajaxUrl : '/wp-admin/admin-ajax.php';

        fetch(ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.disabled = false;
            if (data.success) {
                if (scoreSpan && data.data && typeof data.data.score !== 'undefined') {
                    scoreSpan.textContent = data.data.score.score;
                }
                alert(data.data.message || 'Спасибо за оценку!');
            } else {
                alert(data.data ? data.data.message : 'Ошибка при сохранении оценки.');
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            alert('Сетевая ошибка при отправке оценки.');
        });
    });

    // 2. Client-Side WebP Image Compressor (Up to 15 photos max 1024px)
    window.vbCompressImageToWebP = function(file, maxWidth, quality, callback) {
        maxWidth = maxWidth || 1024;
        quality  = quality || 0.8;

        var reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function(event) {
            var img = new Image();
            img.src = event.target.result;
            img.onload = function() {
                var elem = document.createElement('canvas');
                var width = img.width;
                var height = img.height;

                if (width > maxWidth) {
                    height = Math.round(height * (maxWidth / width));
                    width = maxWidth;
                }

                elem.width = width;
                elem.height = height;

                var ctx = elem.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                elem.toBlob(function(blob) {
                    callback(blob);
                }, 'image/webp', quality);
            };
        };
    };
});
