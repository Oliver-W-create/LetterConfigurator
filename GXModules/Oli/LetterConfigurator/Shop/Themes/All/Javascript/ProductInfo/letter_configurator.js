/* M4.0.3 – Letter Configurator product behaviour */

(function () {
                    'use strict';

                    function initLetterConfigurator() {
                        var root = document.querySelector('#letter-configurator .oli-lc-configurator');
                        if (!root || root.getAttribute('data-oli-lc-initialized') === '1') {
                            return;
                        }
                        root.setAttribute('data-oli-lc-initialized', '1');

                        var materialSelect = root.querySelector('#oli-lc-material');
                        var methodSelect = root.querySelector('#oli-lc-method');
                        var colorSelect = root.querySelector('#oli-lc-color');
                        var thicknessSelect = root.querySelector('#oli-lc-thickness');
                        var textInput = root.querySelector('#oli-lc-text');
                        var widthInput = root.querySelector('#oli-lc-width');
                        var heightInput = root.querySelector('#oli-lc-height');
                        var priceBox = root.querySelector('#oli-lc-price');
                        var priceMessage = root.querySelector('#oli-lc-price-message');
                        var priceDetails = root.querySelector('#oli-lc-price-details');
                        var mainGrossPrice = document.querySelector('#oli-lc-main-price-gross');
                        var svgInput = root.querySelector('#oli-lc-svg');
                        var svgField = root.querySelector('#oli-lc-svg-field');
                        var svgPreview = root.querySelector('#oli-lc-svg-preview');
                        var svgPreviewCanvas = root.querySelector('#oli-lc-svg-preview-canvas');
                        var svgPreviewMeta = root.querySelector('#oli-lc-svg-preview-meta');
                        var svgAnalysis = root.querySelector('#oli-lc-svg-analysis');
                        var svgAnalysisGrid = root.querySelector('#oli-lc-svg-analysis-grid');
                        var svgAnalysisWarnings = root.querySelector('#oli-lc-svg-analysis-warnings');
                        if (!materialSelect || !methodSelect || !colorSelect || !thicknessSelect || !textInput || !widthInput || !heightInput) {
                            return;
                        }


                        function resetSvgPreview() {
                            if (svgField) {
                                svgField.classList.remove('is-invalid');
                            }
                            if (svgPreview) {
                                svgPreview.classList.remove('is-visible');
                            }
                            if (svgPreviewCanvas) {
                                svgPreviewCanvas.innerHTML = '';
                            }
                            if (svgPreviewMeta) {
                                svgPreviewMeta.textContent = '';
                            }
                            if (svgAnalysis) {
                                svgAnalysis.classList.remove('is-visible');
                            }
                            if (svgAnalysisGrid) {
                                svgAnalysisGrid.innerHTML = '';
                            }
                            if (svgAnalysisWarnings) {
                                svgAnalysisWarnings.textContent = '';
                            }
                        }

                        function parseSvgLength(value) {
                            var match = String(value || '').trim().match(/^([+-]?(?:\d+\.?\d*|\.\d+))(px|mm|cm|in|pt|pc)?$/i);
                            if (!match) { return null; }
                            return {value: parseFloat(match[1]), unit: (match[2] || '').toLowerCase()};
                        }

                        function lengthToMm(length) {
                            if (!length || !isFinite(length.value)) { return null; }
                            var factors = {mm:1, cm:10, in:25.4, pt:25.4/72, pc:25.4/6, px:25.4/96, '':25.4/96};
                            return Object.prototype.hasOwnProperty.call(factors, length.unit) ? length.value * factors[length.unit] : null;
                        }

                        function formatNumber(value, decimals) {
                            if (!isFinite(value)) { return '–'; }
                            return value.toLocaleString('de-DE', {minimumFractionDigits: decimals, maximumFractionDigits: decimals});
                        }

                        function isClosedGeometry(element) {
                            var name = element.nodeName.toLowerCase();
                            if (name === 'rect' || name === 'circle' || name === 'ellipse' || name === 'polygon') { return true; }
                            if (name === 'path') { return /[zZ]\s*$/.test(element.getAttribute('d') || ''); }
                            return false;
                        }

                        function transformedPoint(element, point) {
                            var matrix = element.getCTM ? element.getCTM() : null;
                            if (!matrix) { return {x:point.x, y:point.y}; }
                            return {x:matrix.a * point.x + matrix.c * point.y + matrix.e, y:matrix.b * point.x + matrix.d * point.y + matrix.f};
                        }

                        function sampleGeometry(element, closed) {
                            if (typeof element.getTotalLength !== 'function' || typeof element.getPointAtLength !== 'function') {
                                return null;
                            }
                            var length = element.getTotalLength();
                            if (!isFinite(length) || length <= 0) { return null; }
                            var steps = Math.max(12, Math.min(3000, Math.ceil(length / 1.5)));
                            var points = [];
                            var total = 0;
                            var previous = null;
                            for (var i = 0; i <= steps; i += 1) {
                                var local = element.getPointAtLength(length * i / steps);
                                var point = transformedPoint(element, local);
                                points.push(point);
                                if (previous) {
                                    total += Math.hypot(point.x - previous.x, point.y - previous.y);
                                }
                                previous = point;
                            }
                            var area = 0;
                            if (closed && points.length > 2) {
                                for (var j = 0; j < points.length - 1; j += 1) {
                                    area += points[j].x * points[j + 1].y - points[j + 1].x * points[j].y;
                                }
                                area = Math.abs(area / 2);
                            }
                            return {length:total, area:area, points:points};
                        }

                        function addAnalysisRow(label, value, section) {
                            if (!svgAnalysisGrid) { return; }
                            if (section) {
                                var heading = document.createElement('dt');
                                heading.className = 'oli-lc-svg-analysis__section';
                                heading.textContent = section;
                                svgAnalysisGrid.appendChild(heading);
                                var blank = document.createElement('dd');
                                blank.hidden = true;
                                svgAnalysisGrid.appendChild(blank);
                            }
                            var dt = document.createElement('dt');
                            var dd = document.createElement('dd');
                            dt.textContent = label;
                            dd.textContent = value;
                            svgAnalysisGrid.appendChild(dt);
                            svgAnalysisGrid.appendChild(dd);
                        }

                        function analyseSvg(svg, file) {
                            if (!svgAnalysis || !svgAnalysisGrid) { return; }
                            svgAnalysisGrid.innerHTML = '';
                            var warnings = [];
                            var viewBoxValues = String(svg.getAttribute('viewBox') || '').trim().split(/[ ,]+/).map(Number);
                            var hasViewBox = viewBoxValues.length === 4 && viewBoxValues.every(isFinite) && viewBoxValues[2] > 0 && viewBoxValues[3] > 0;
                            var widthLength = parseSvgLength(svg.getAttribute('data-oli-original-width') || svg.getAttribute('width'));
                            var heightLength = parseSvgLength(svg.getAttribute('data-oli-original-height') || svg.getAttribute('height'));
                            var widthMm = lengthToMm(widthLength);
                            var heightMm = lengthToMm(heightLength);
                            var scaleX = hasViewBox && widthMm ? widthMm / viewBoxValues[2] : null;
                            var scaleY = hasViewBox && heightMm ? heightMm / viewBoxValues[3] : null;
                            if (!hasViewBox) { warnings.push('Keine gültige ViewBox vorhanden. Physische Geometriedaten können dadurch ungenau sein.'); }
                            if (!widthMm || !heightMm) { warnings.push('Breite oder Höhe fehlen bzw. haben keine unterstützte Einheit; Längen werden in SVG-Einheiten angezeigt.'); }
                            if (scaleX && scaleY && Math.abs(scaleX - scaleY) > Math.max(scaleX, scaleY) * 0.01) { warnings.push('Unterschiedliche X-/Y-Skalierung erkannt. Konturlänge und Fläche werden entsprechend skaliert angenähert.'); }

                            var selector = 'path,rect,circle,ellipse,line,polyline,polygon';
                            var geometries = Array.prototype.slice.call(svg.querySelectorAll(selector)).filter(function (element) {
                                var style = window.getComputedStyle ? window.getComputedStyle(element) : null;
                                return !style || (style.display !== 'none' && style.visibility !== 'hidden');
                            });
                            var counts = {path:0,rect:0,circle:0,ellipse:0,line:0,polyline:0,polygon:0};
                            var openCount = 0, closedCount = 0, totalLengthUnits = 0, totalAreaUnits = 0;
                            var minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
                            geometries.forEach(function (element) {
                                var name = element.nodeName.toLowerCase();
                                counts[name] += 1;
                                var closed = isClosedGeometry(element);
                                if (closed) { closedCount += 1; } else { openCount += 1; }
                                var sampled = sampleGeometry(element, closed);
                                if (sampled) {
                                    totalLengthUnits += sampled.length;
                                    totalAreaUnits += sampled.area;
                                    sampled.points.forEach(function (point) {
                                        minX = Math.min(minX, point.x); minY = Math.min(minY, point.y);
                                        maxX = Math.max(maxX, point.x); maxY = Math.max(maxY, point.y);
                                    });
                                }
                            });
                            var unsupported = svg.querySelectorAll('text,image,use,symbol,marker,pattern,mask,clipPath').length;
                            if (unsupported) { warnings.push(unsupported + ' nicht direkt auswertbare Elemente erkannt (z. B. Text, Bild, Use oder Masken). Vor der Fertigung sollten diese in Pfade umgewandelt werden.'); }
                            if (!geometries.length) { warnings.push('Keine auswertbaren Konturelemente gefunden.'); }
                            if (isFinite(minX) && (minX < 0 || minY < 0)) { warnings.push('Negative Koordinaten erkannt.'); }
                            if (openCount) { warnings.push(openCount + ' offene Kontur(en) erkannt.'); }

                            var avgScale = scaleX && scaleY ? (scaleX + scaleY) / 2 : null;
                            var lengthText = avgScale ? formatNumber(totalLengthUnits * avgScale, 2) + ' mm' : formatNumber(totalLengthUnits, 2) + ' SVG-Einheiten';
                            var areaText = scaleX && scaleY ? formatNumber(totalAreaUnits * scaleX * scaleY, 2) + ' mm²' : formatNumber(totalAreaUnits, 2) + ' SVG-Einheiten²';
                            var bboxText = isFinite(minX) ? formatNumber(maxX - minX, 2) + ' × ' + formatNumber(maxY - minY, 2) + (avgScale ? ' SVG-Einheiten' : ' SVG-Einheiten') : '–';
                            if (scaleX && scaleY && isFinite(minX)) { bboxText = formatNumber((maxX-minX)*scaleX, 2) + ' × ' + formatNumber((maxY-minY)*scaleY, 2) + ' mm'; }

                            addAnalysisRow('Datei', file.name, 'Datei');
                            addAnalysisRow('Dateigröße', Math.max(1, Math.round(file.size / 1024)) + ' KB');
                            addAnalysisRow('Breite', widthLength ? formatNumber(widthLength.value, 2) + (widthLength.unit || ' px') : '–');
                            addAnalysisRow('Höhe', heightLength ? formatNumber(heightLength.value, 2) + (heightLength.unit || ' px') : '–');
                            addAnalysisRow('ViewBox', hasViewBox ? viewBoxValues.join(' ') : 'nicht vorhanden');
                            addAnalysisRow('Pfade', String(counts.path), 'Geometrie');
                            addAnalysisRow('Rechtecke / Kreise / Ellipsen', counts.rect + ' / ' + counts.circle + ' / ' + counts.ellipse);
                            addAnalysisRow('Linien / Polylinien / Polygone', counts.line + ' / ' + counts.polyline + ' / ' + counts.polygon);
                            addAnalysisRow('Gruppen', String(svg.querySelectorAll('g').length));
                            addAnalysisRow('Transformationen', String(svg.querySelectorAll('[transform]').length));
                            addAnalysisRow('Geschlossene Konturen', String(closedCount), 'Kontur und Fläche');
                            addAnalysisRow('Offene Konturen', String(openCount));
                            addAnalysisRow('Gesamte Konturlänge', lengthText);
                            addAnalysisRow('Näherungsfläche', areaText);
                            addAnalysisRow('Bounding Box', bboxText);
                            if (svgAnalysisWarnings) { svgAnalysisWarnings.textContent = warnings.join(' '); }
                            svgAnalysis.classList.add('is-visible');
                        }

                        function sanitizeSvgDocument(documentNode) {
                            var forbidden = documentNode.querySelectorAll('script, foreignObject, iframe, object, embed, audio, video');
                            Array.prototype.forEach.call(forbidden, function (node) { node.remove(); });
                            var all = documentNode.querySelectorAll('*');
                            Array.prototype.forEach.call(all, function (node) {
                                Array.prototype.slice.call(node.attributes || []).forEach(function (attribute) {
                                    var name = attribute.name.toLowerCase();
                                    var value = String(attribute.value || '').trim().toLowerCase();
                                    if (name.indexOf('on') === 0 || ((name === 'href' || name === 'xlink:href') && value && value.charAt(0) !== '#') || value.indexOf('javascript:') === 0) {
                                        node.removeAttribute(attribute.name);
                                    }
                                });
                            });
                            return documentNode.documentElement;
                        }

                        function handleSvgSelection() {
                            resetSvgPreview();
                            if (!svgInput || !svgInput.files || !svgInput.files.length) {
                                return;
                            }
                            var file = svgInput.files[0];
                            var validName = /\.svg$/i.test(file.name || '');
                            var validType = !file.type || file.type === 'image/svg+xml';
                            if (!validName || !validType || file.size <= 0 || file.size > 2 * 1024 * 1024) {
                                if (svgField) { svgField.classList.add('is-invalid'); }
                                svgInput.value = '';
                                return;
                            }
                            var reader = new FileReader();
                            reader.onload = function () {
                                try {
                                    var parser = new DOMParser();
                                    var documentNode = parser.parseFromString(String(reader.result || ''), 'image/svg+xml');
                                    if (documentNode.querySelector('parsererror') || !documentNode.documentElement || documentNode.documentElement.nodeName.toLowerCase() !== 'svg') {
                                        throw new Error('Invalid SVG');
                                    }
                                    var safeSvg = sanitizeSvgDocument(documentNode);
                                    var previewSvg = document.importNode(safeSvg, true);
                                    if (previewSvg.hasAttribute('width')) { previewSvg.setAttribute('data-oli-original-width', previewSvg.getAttribute('width')); }
                                    if (previewSvg.hasAttribute('height')) { previewSvg.setAttribute('data-oli-original-height', previewSvg.getAttribute('height')); }
                                    previewSvg.removeAttribute('width');
                                    previewSvg.removeAttribute('height');
                                    previewSvg.setAttribute('role', 'img');
                                    previewSvg.setAttribute('aria-label', 'SVG-Vorschau');
                                    if (svgPreviewCanvas) {
                                        svgPreviewCanvas.appendChild(previewSvg);
                                    }
                                    if (svgPreviewMeta) {
                                        svgPreviewMeta.textContent = file.name + ' · ' + Math.max(1, Math.round(file.size / 1024)) + ' KB';
                                    }
                                    if (svgPreview) {
                                        svgPreview.classList.add('is-visible');
                                    }
                                    window.requestAnimationFrame(function () {
                                        var renderedSvg = svgPreviewCanvas ? svgPreviewCanvas.querySelector('svg') : null;
                                        if (renderedSvg) { analyseSvg(renderedSvg, file); }
                                    });
                                } catch (error) {
                                    if (svgField) { svgField.classList.add('is-invalid'); }
                                    svgInput.value = '';
                                }
                            };
                            reader.onerror = function () {
                                if (svgField) { svgField.classList.add('is-invalid'); }
                                svgInput.value = '';
                            };
                            reader.readAsText(file);
                        }

                        function captureOptions(select) {
                            return Array.prototype.slice.call(select.options, 1).map(function (option) {
                                return {
                                    value: option.value,
                                    text: option.text,
                                    materialId: option.getAttribute('data-material-id') || '',
                                    methodId: option.getAttribute('data-method-id') || ''
                                };
                            });
                        }

                        var materialOptions = captureOptions(materialSelect);
                        var methodOptions = captureOptions(methodSelect);
                        var colorOptions = captureOptions(colorSelect);
                        var thicknessOptions = captureOptions(thicknessSelect);

                        function hasCombination(materialId, methodId) {
                            return thicknessOptions.some(function (option) {
                                return (!materialId || option.materialId === materialId)
                                    && (!methodId || option.methodId === methodId);
                            });
                        }

                        function rebuild(select, options, predicate, placeholder) {
                            var previousValue = select.value;
                            select.innerHTML = '';

                            var emptyOption = document.createElement('option');
                            emptyOption.value = '';
                            emptyOption.textContent = placeholder;
                            select.appendChild(emptyOption);

                            options.filter(predicate).forEach(function (item) {
                                var option = document.createElement('option');
                                option.value = item.value;
                                option.textContent = item.text;
                                if (item.materialId) {
                                    option.setAttribute('data-material-id', item.materialId);
                                }
                                if (item.methodId) {
                                    option.setAttribute('data-method-id', item.methodId);
                                }
                                select.appendChild(option);
                            });

                            if (previousValue && Array.prototype.some.call(select.options, function (option) { return option.value === previousValue; })) {
                                select.value = previousValue;
                            } else if (select.options.length === 2) {
                                // A single valid option is unambiguous and can be selected automatically.
                                select.selectedIndex = 1;
                            } else {
                                select.value = '';
                            }
                        }

                        function updateSelections() {
                            var selectedMaterial = materialSelect.value;
                            var selectedMethod = methodSelect.value;

                            rebuild(materialSelect, materialOptions, function (option) {
                                return !selectedMethod || hasCombination(option.value, selectedMethod);
                            }, 'Bitte auswählen');

                            selectedMaterial = materialSelect.value;
                            rebuild(methodSelect, methodOptions, function (option) {
                                return !selectedMaterial || hasCombination(selectedMaterial, option.value);
                            }, 'Bitte auswählen');

                            selectedMethod = methodSelect.value;
                            rebuild(colorSelect, colorOptions, function (option) {
                                return !!selectedMaterial && option.materialId === selectedMaterial;
                            }, selectedMaterial ? 'Bitte auswählen' : 'Zuerst Material auswählen');
                            colorSelect.disabled = !selectedMaterial;

                            rebuild(thicknessSelect, thicknessOptions, function (option) {
                                return !!selectedMaterial && !!selectedMethod
                                    && option.materialId === selectedMaterial
                                    && option.methodId === selectedMethod;
                            }, selectedMaterial && selectedMethod ? 'Bitte auswählen' : 'Zuerst Material und Fertigungsart auswählen');
                            thicknessSelect.disabled = !(selectedMaterial && selectedMethod);
                        }

                        function numberValue(name) {
                            var value = parseFloat(root.getAttribute(name) || '0');
                            return isFinite(value) && value > 0 ? value : 0;
                        }

                        function money(value) {
                            return (Math.round(value * 100) / 100).toLocaleString('de-DE', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + ' €';
                        }

                        function setPrice(id, value) {
                            var element = root.querySelector(id);
                            if (element) {
                                element.textContent = money(value);
                            }
                        }

                        function resetMainPrice() {
                            if (mainGrossPrice) {
                                mainGrossPrice.textContent = 'Preis nach Konfiguration';
                            }
                        }

                        var form = root.closest('form');
                        var submitButtons = form ? Array.prototype.slice.call(form.querySelectorAll('button[type="submit"], input[type="submit"]')) : [];
                        var minDimension = parseFloat(root.getAttribute('data-dimension-min') || '1');
                        var maxDimension = parseFloat(root.getAttribute('data-dimension-max') || '100000');
                        var maxTextLength = parseInt(root.getAttribute('data-text-max-length') || '255', 10);

                        function markField(control, valid, showErrors) {
                            var field = control.closest('.oli-lc-configurator__field');
                            if (field) {
                                field.classList.toggle('is-invalid', !!showErrors && !valid);
                            }
                            control.setAttribute('aria-invalid', valid ? 'false' : 'true');
                        }

                        function validationState(showErrors) {
                            var text = textInput.value.trim();
                            var width = parseFloat(String(widthInput.value).replace(',', '.'));
                            var height = parseFloat(String(heightInput.value).replace(',', '.'));
                            var states = [
                                [textInput, text.length > 0 && text.length <= maxTextLength],
                                [widthInput, isFinite(width) && width >= minDimension && width <= maxDimension],
                                [heightInput, isFinite(height) && height >= minDimension && height <= maxDimension],
                                [materialSelect, !!materialSelect.value],
                                [methodSelect, !!methodSelect.value],
                                [colorSelect, !!colorSelect.value],
                                [thicknessSelect, !!thicknessSelect.value]
                            ];
                            states.forEach(function (state) { markField(state[0], state[1], showErrors); });
                            var valid = states.every(function (state) { return state[1]; });
                            submitButtons.forEach(function (button) {
                                button.disabled = !valid;
                                button.setAttribute('aria-disabled', valid ? 'false' : 'true');
                            });
                            return valid;
                        }

                        function calculatePrice(showErrors) {
                            if (!priceBox || !priceMessage || !priceDetails) {
                                return;
                            }
                            priceBox.classList.add('is-visible');

                            var profileMaterialId = root.getAttribute('data-price-material-id') || '';
                            var profileMethodId = root.getAttribute('data-price-method-id') || '';
                            if (!profileMaterialId || !profileMethodId) {
                                priceMessage.textContent = 'Für diese Produktvorlage ist kein aktives Preisprofil hinterlegt.';
                                priceMessage.hidden = false;
                                priceDetails.hidden = true;
                                resetMainPrice();
                                return;
                            }

                            var width = parseFloat(String(widthInput.value).replace(',', '.')) || 0;
                            var height = parseFloat(String(heightInput.value).replace(',', '.')) || 0;
                            var text = textInput.value.trim();
                            var complete = validationState(!!showErrors);
                            if (!complete) {
                                priceMessage.textContent = 'Bitte Text, Maße und alle Ausführungsmerkmale auswählen.';
                                priceMessage.hidden = false;
                                priceDetails.hidden = true;
                                resetMainPrice();
                                return;
                            }

                            if (materialSelect.value !== profileMaterialId || methodSelect.value !== profileMethodId) {
                                priceMessage.textContent = 'Das hinterlegte Preisprofil gilt nicht für diese Material-/Fertigungsart-Kombination.';
                                priceMessage.hidden = false;
                                priceDetails.hidden = true;
                                resetMainPrice();
                                return;
                            }

                            var areaM2 = (width * height) / 1000000;
                            var perimeterMm = 2 * (width + height);
                            var wasteFactor = 1 + (numberValue('data-waste-percent') / 100);
                            var characterCount = text.replace(/\s/g, '').length;

                            var materialPrice = areaM2 * numberValue('data-area-price') * wasteFactor;
                            var contourPrice = perimeterMm * numberValue('data-contour-price');
                            var characterPrice = characterCount * numberValue('data-character-price');
                            var fixedPrice = numberValue('data-fixed-price');
                            var setupFee = numberValue('data-setup-fee');
                            var minimumPrice = numberValue('data-minimum-price');
                            var netPrice = materialPrice + contourPrice + characterPrice + fixedPrice + setupFee;
                            netPrice = Math.max(netPrice, minimumPrice);
                            var grossPrice = netPrice * (1 + numberValue('data-tax-rate') / 100);

                            setPrice('#oli-lc-price-material', materialPrice);
                            setPrice('#oli-lc-price-contour', contourPrice);
                            setPrice('#oli-lc-price-characters', characterPrice);
                            setPrice('#oli-lc-price-fixed', fixedPrice);
                            setPrice('#oli-lc-price-setup', setupFee);
                            setPrice('#oli-lc-price-net', netPrice);
                            var gross = root.querySelector('#oli-lc-price-gross');
                            if (gross) {
                                gross.textContent = money(grossPrice);
                            }
                            if (mainGrossPrice) {
                                mainGrossPrice.textContent = money(grossPrice);
                            }
                            priceMessage.hidden = true;
                            priceDetails.hidden = false;
                        }

                        function updateAll() {
                            updateSelections();
                            calculatePrice();
                        }

                        materialSelect.addEventListener('change', updateAll);
                        methodSelect.addEventListener('change', updateAll);
                        colorSelect.addEventListener('change', function () { calculatePrice(false); });
                        thicknessSelect.addEventListener('change', function () { calculatePrice(false); });
                        textInput.addEventListener('input', function () { calculatePrice(false); });
                        widthInput.addEventListener('input', function () { calculatePrice(false); });
                        heightInput.addEventListener('input', function () { calculatePrice(false); });
                        if (svgInput) {
                            svgInput.addEventListener('change', handleSvgSelection);
                        }

                        if (form) {
                            form.addEventListener('submit', function (event) {
                                calculatePrice(true);
                                if (!validationState(true) || priceDetails.hidden) {
                                    event.preventDefault();
                                    priceMessage.textContent = 'Bitte die Konfiguration vollständig und gültig ausfüllen.';
                                    priceMessage.hidden = false;
                                    root.scrollIntoView({behavior: 'smooth', block: 'center'});
                                }
                            });
                        }
                        updateAll();
                        validationState(false);
                    }

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', initLetterConfigurator);
                    } else {
                        initLetterConfigurator();
                    }
                }());
