<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { WandSparkles } from '@lucide/vue';

const props = defineProps({
    sourceFile: { type: File, default: null },
    sourceUrl: { type: String, default: '' },
    selectedCutout: { type: File, default: null },
});

const emit = defineEmits(['cutout']);
const tolerance = ref(54);
const processing = ref(false);
const errorMessage = ref('');
const resultUrl = ref('');
const resultName = ref('');
let resultObjectUrl = '';
let generatedFile = null;
let generation = 0;

const canGenerate = computed(() => Boolean(props.sourceFile || props.sourceUrl) && !processing.value);

watch([() => props.sourceFile, () => props.sourceUrl], () => {
    generation += 1;
    processing.value = false;
    errorMessage.value = '';
    resultName.value = '';
    generatedFile = null;

    if (resultObjectUrl) {
        URL.revokeObjectURL(resultObjectUrl);
        resultObjectUrl = '';
    }

    resultUrl.value = '';
});

watch(() => props.selectedCutout, (file) => {
    if ((file && file !== generatedFile) || (!file && generatedFile)) {
        generatedFile = null;
        resultName.value = '';

        if (resultObjectUrl) {
            URL.revokeObjectURL(resultObjectUrl);
            resultObjectUrl = '';
        }

        resultUrl.value = '';
    }
});

function getCornerColors(data, width, height) {
    const colors = [];
    const sampleSize = Math.max(1, Math.min(12, Math.floor(Math.min(width, height) * 0.025)));

    for (const [startX, startY] of [
        [0, 0],
        [width - sampleSize, 0],
        [0, height - sampleSize],
        [width - sampleSize, height - sampleSize],
    ]) {
        let red = 0;
        let green = 0;
        let blue = 0;
        let count = 0;

        for (let y = startY; y < startY + sampleSize; y += 1) {
            for (let x = startX; x < startX + sampleSize; x += 1) {
                const offset = (y * width + x) * 4;
                red += data[offset];
                green += data[offset + 1];
                blue += data[offset + 2];
                count += 1;
            }
        }

        colors.push([red / count, green / count, blue / count]);
    }

    return colors;
}

function colorDistance(data, offset, colors) {
    let closest = Number.POSITIVE_INFINITY;

    for (const [red, green, blue] of colors) {
        const redDelta = data[offset] - red;
        const greenDelta = data[offset + 1] - green;
        const blueDelta = data[offset + 2] - blue;
        closest = Math.min(closest, Math.sqrt(redDelta ** 2 + greenDelta ** 2 + blueDelta ** 2));
    }

    return closest;
}

function createBackgroundMask(data, width, height, colors, colorTolerance) {
    const pixelCount = width * height;
    const background = new Uint8Array(pixelCount);
    const queue = new Int32Array(pixelCount);
    let queueStart = 0;
    let queueEnd = 0;

    const enqueueIfBackground = (pixelIndex) => {
        if (background[pixelIndex]) {
            return;
        }

        const offset = pixelIndex * 4;
        if (data[offset + 3] === 0 || colorDistance(data, offset, colors) <= colorTolerance) {
            background[pixelIndex] = 1;
            queue[queueEnd] = pixelIndex;
            queueEnd += 1;
        }
    };

    for (let x = 0; x < width; x += 1) {
        enqueueIfBackground(x);
        enqueueIfBackground((height - 1) * width + x);
    }

    for (let y = 1; y < height - 1; y += 1) {
        enqueueIfBackground(y * width);
        enqueueIfBackground(y * width + width - 1);
    }

    while (queueStart < queueEnd) {
        const pixelIndex = queue[queueStart];
        queueStart += 1;
        const x = pixelIndex % width;
        const y = Math.floor(pixelIndex / width);

        if (x > 0) {
            enqueueIfBackground(pixelIndex - 1);
        }
        if (x < width - 1) {
            enqueueIfBackground(pixelIndex + 1);
        }
        if (y > 0) {
            enqueueIfBackground(pixelIndex - width);
        }
        if (y < height - 1) {
            enqueueIfBackground(pixelIndex + width);
        }
    }

    return background;
}

function loadSourceImage(sourceFile, sourceUrl) {
    return new Promise((resolve, reject) => {
        const image = new Image();
        const temporaryUrl = sourceFile ? URL.createObjectURL(sourceFile) : '';
        image.crossOrigin = 'anonymous';
        image.onload = () => {
            if (temporaryUrl) {
                URL.revokeObjectURL(temporaryUrl);
            }
            resolve(image);
        };
        image.onerror = () => {
            if (temporaryUrl) {
                URL.revokeObjectURL(temporaryUrl);
            }
            reject(new Error('Foto asli tidak dapat dibuka untuk diproses.'));
        };
        image.src = temporaryUrl || sourceUrl;
    });
}

function canvasBlob(canvas, type, quality) {
    return new Promise((resolve, reject) => {
        canvas.toBlob((blob) => {
            if (blob) {
                resolve(blob);
                return;
            }

            reject(new Error('Hasil cutout tidak dapat dibuat oleh browser ini.'));
        }, type, quality);
    });
}

async function generateCutout() {
    if (!canGenerate.value) {
        return;
    }

    processing.value = true;
    errorMessage.value = '';
    const operation = generation + 1;
    generation = operation;
    const sourceFile = props.sourceFile;
    const sourceUrl = props.sourceUrl;

    try {
        const image = await loadSourceImage(sourceFile, sourceUrl);
        if (operation !== generation) {
            return;
        }

        const scale = Math.min(1, 1200 / Math.max(image.naturalWidth, image.naturalHeight));
        const width = Math.max(1, Math.round(image.naturalWidth * scale));
        const height = Math.max(1, Math.round(image.naturalHeight * scale));
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const context = canvas.getContext('2d', { willReadFrequently: true });

        if (!context) {
            throw new Error('Canvas tidak tersedia untuk memproses foto.');
        }

        context.drawImage(image, 0, 0, width, height);
        const imageData = context.getImageData(0, 0, width, height);
        const cornerColors = getCornerColors(imageData.data, width, height);
        const background = createBackgroundMask(imageData.data, width, height, cornerColors, tolerance.value);
        let removedPixels = 0;

        for (let pixel = 0; pixel < background.length; pixel += 1) {
            if (background[pixel]) {
                imageData.data[pixel * 4 + 3] = 0;
                removedPixels += 1;
            }
        }

        if (removedPixels < width * height * 0.005) {
            throw new Error('Latar seragam tidak terdeteksi. Coba atur ambang atau unggah cutout manual.');
        }

        context.putImageData(imageData, 0, 0);
        let blob = await canvasBlob(canvas, 'image/webp', 0.86);
        if (operation !== generation) {
            return;
        }

        if (blob.type !== 'image/webp') {
            blob = await canvasBlob(canvas, 'image/png');
        }

        if (blob.size > 5 * 1024 * 1024) {
            throw new Error('Hasil lebih dari 5 MB. Gunakan cutout manual dengan ukuran lebih kecil.');
        }

        const sourceName = sourceFile?.name || 'anggota';
        const baseName = sourceName.replace(/\.[^.]+$/, '').replace(/[^a-zA-Z0-9_-]/g, '-');
        const extension = blob.type === 'image/webp' ? 'webp' : 'png';
        const file = new File([blob], `${baseName}-cutout.${extension}`, { type: blob.type });
        if (operation !== generation) {
            return;
        }

        if (resultObjectUrl) {
            URL.revokeObjectURL(resultObjectUrl);
        }
        resultObjectUrl = URL.createObjectURL(file);
        resultUrl.value = resultObjectUrl;
        resultName.value = file.name;
        generatedFile = file;
        emit('cutout', file);
    } catch (error) {
        if (operation === generation) {
            errorMessage.value = error instanceof Error ? error.message : 'Pembuatan cutout gagal. Silakan unggah manual.';
        }
    } finally {
        if (operation === generation) {
            processing.value = false;
        }
    }
}

onBeforeUnmount(() => {
    if (resultObjectUrl) {
        URL.revokeObjectURL(resultObjectUrl);
    }
});
</script>

<template>
    <section class="grid gap-3 border-t border-[#d8d1c6] pt-4" aria-label="Pembuatan cutout otomatis">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#73552f]">Proses lokal di browser</p>
                <p class="mt-1 text-xs leading-5 text-[#55514b]">Menghapus latar seragam dari tepi foto; hasil tetap perlu diperiksa.</p>
            </div>
            <button
                type="button"
                class="admin-button-secondary focus-ring"
                :disabled="!canGenerate"
                @click="generateCutout"
            >
                <WandSparkles :size="15" />
                {{ processing ? 'Memproses…' : 'BUAT OTOMATIS DARI FOTO ASLI' }}
            </button>
        </div>
        <label class="grid gap-2 text-xs text-[#55514b]">
            Ambang penghapusan latar
            <input v-model.number="tolerance" type="range" min="24" max="112" step="2" :disabled="processing" aria-label="Ambang penghapusan latar">
            <span>{{ tolerance }} / 112 · ambang lebih tinggi menghapus warna latar yang lebih beragam.</span>
        </label>
        <div v-if="resultUrl" class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center">
            <div class="relative grid min-h-40 place-items-center overflow-hidden border border-[#d8d1c6] bg-[conic-gradient(#4a4a4a_25%,#242424_0_50%,#4a4a4a_0_75%,#242424_0)] bg-[length:24px_24px]">
                <img :src="resultUrl" alt="Pratinjau hasil cutout otomatis" class="max-h-64 max-w-full object-contain">
            </div>
            <p class="break-all text-xs text-[#55514b]">Siap disimpan: {{ resultName }}</p>
        </div>
        <p v-if="errorMessage" class="text-xs text-[#713a35]" role="alert">{{ errorMessage }}</p>
    </section>
</template>
